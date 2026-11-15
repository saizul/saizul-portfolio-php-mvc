<?php declare(strict_types=1);
namespace App\Core;

/**
 * Upload — handles single and bulk file uploads.
 * Converts images to WebP (if GD available), compresses, validates.
 * Validation rules live in App\Core\UploadPolicy.
 */
class Upload {

    /** @var array<string,string> original filename => reason, from the last handleMultiple() call */
    private static array $rejected = [];

    /**
     * Handle a single $_FILES field.
     *
     * @param  string $fileKey   $_FILES key
     * @param  string $folder    Sub-folder under public/uploads/
     * @param  bool   $toWebP    Convert images to WebP
     * @return array  ['path'=>string, 'webp_path'=>string|null, 'width'=>int|null, 'height'=>int|null, 'mime'=>string, 'size'=>int, 'filename'=>string]
     */
    public static function handle(string $fileKey, string $folder = 'general', bool $toWebP = true): array {
        if (empty($_FILES[$fileKey]) || $_FILES[$fileKey]['error'] !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('Upload error: ' . ($_FILES[$fileKey]['error'] ?? 'no file'));
        }
        return self::processFile($_FILES[$fileKey], $folder, $toWebP);
    }

    /**
     * Handle multiple files from a single <input type="file" multiple>.
     * Returns array of result arrays.
     */
    public static function handleMultiple(string $fileKey, string $folder = 'general', bool $toWebP = true): array {
        self::$rejected = [];
        if (empty($_FILES[$fileKey])) return [];

        $files   = $_FILES[$fileKey];
        $results = [];
        $count   = is_array($files['name']) ? count($files['name']) : 1;

        for ($i = 0; $i < $count; $i++) {
            $file = [
                'name'     => is_array($files['name'])     ? $files['name'][$i]     : $files['name'],
                'type'     => is_array($files['type'])     ? $files['type'][$i]     : $files['type'],
                'tmp_name' => is_array($files['tmp_name']) ? $files['tmp_name'][$i] : $files['tmp_name'],
                'error'    => is_array($files['error'])    ? $files['error'][$i]    : $files['error'],
                'size'     => is_array($files['size'])     ? $files['size'][$i]     : $files['size'],
            ];
            $label = UploadPolicy::cleanOriginalName((string) $file['name']);
            if ($file['error'] !== UPLOAD_ERR_OK) {
                if ($file['error'] !== UPLOAD_ERR_NO_FILE) {
                    self::$rejected[$label] = 'Upload error: ' . $file['error'];
                }
                continue;
            }
            try {
                $results[] = self::processFile($file, $folder, $toWebP);
            } catch (\Throwable $e) {
                // Skip the file but remember why, so the caller can report it.
                self::$rejected[$label] = $e->getMessage();
            }
        }
        return $results;
    }

    /**
     * Validate an uploaded file without moving it.
     *
     * @return array{mime:string, ext:string, width:int|null, height:int|null}
     * @throws \RuntimeException when the file breaks any UploadPolicy rule
     */
    public static function inspect(string $tmpPath, string $clientName, int $size): array {
        UploadPolicy::assertSize($size);

        // finfo takes precedence over the browser-reported type and the filename.
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $head  = (string) @file_get_contents($tmpPath, false, null, 0, 1024);
        $mime  = UploadPolicy::refineMime((string) $finfo->file($tmpPath), $clientName, $head);
        $ext   = UploadPolicy::extensionFor($mime);

        $width = $height = null;
        if (UploadPolicy::isRaster($mime)) {
            $dims = @getimagesize($tmpPath);
            if (!$dims) {
                throw new \RuntimeException('File is not a readable image.');
            }
            UploadPolicy::assertDimensions((int) $dims[0], (int) $dims[1]);
            [$width, $height] = [(int) $dims[0], (int) $dims[1]];
        } elseif ($mime === 'image/svg+xml') {
            UploadPolicy::assertSafeSvg((string) file_get_contents($tmpPath));
        }

        return ['mime' => $mime, 'ext' => $ext, 'width' => $width, 'height' => $height];
    }

    /** Reasons for files skipped by the last handleMultiple() call, keyed by original name. */
    public static function lastRejected(): array { return self::$rejected; }

    private static function processFile(array $file, string $folder, bool $toWebP): array {
        if (!is_uploaded_file($file['tmp_name'])) {
            throw new \RuntimeException('Not an uploaded file.');
        }
        $folder   = UploadPolicy::sanitizeFolder($folder);
        $info     = self::inspect($file['tmp_name'], (string) $file['name'], (int) $file['size']);
        $realMime = $info['mime'];

        // Build upload path; the uploads root gets a no-execute .htaccess.
        $uploadsRoot = BASE_PATH . '/public/uploads';
        self::ensureUploadsRoot($uploadsRoot);
        $uploadDir = "{$uploadsRoot}/{$folder}";
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
            throw new \RuntimeException('Could not create upload folder.');
        }

        // Extension comes from the sniffed type, never from the client filename.
        $safeName = bin2hex(random_bytes(8)) . '.' . $info['ext'];
        $destPath = "{$uploadDir}/{$safeName}";
        $webPath  = "/uploads/{$folder}/{$safeName}";

        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            throw new \RuntimeException('Could not move uploaded file.');
        }
        @chmod($destPath, 0644);

        $result = [
            'path'      => $webPath,
            'webp_path' => null,
            'width'     => $info['width'],
            'height'    => $info['height'],
            'mime'      => $realMime,
            'size'      => (int) $file['size'],
            'filename'  => UploadPolicy::cleanOriginalName((string) $file['name']),
            'folder'    => $folder,
        ];

        // Image processing
        if (UploadPolicy::isRaster($realMime) && function_exists('imagecreatefromjpeg')) {
            // WebP conversion
            if ($toWebP && $realMime !== 'image/webp' && $realMime !== 'image/svg+xml') {
                $webpName = bin2hex(random_bytes(8)) . '.webp';
                $webpDest = "{$uploadDir}/{$webpName}";
                $webpWeb  = "/uploads/{$folder}/{$webpName}";

                if (self::convertToWebP($destPath, $webpDest, $realMime)) {
                    $result['webp_path'] = $webpWeb;
                }
            }

            // Compress original if large
            if ((int) $file['size'] > 500_000 && $realMime !== 'image/webp') {
                self::compressImage($destPath, $realMime, 82);
            }
        }

        return $result;
    }

    private static function convertToWebP(string $src, string $dest, string $mime): bool {
        try {
            $img = match($mime) {
                'image/jpeg' => @imagecreatefromjpeg($src),
                'image/png'  => @imagecreatefrompng($src),
                'image/gif'  => @imagecreatefromgif($src),
                default      => false,
            };
            if (!$img) return false;
            $ok = imagewebp($img, $dest, 82);
            imagedestroy($img);
            return $ok;
        } catch (\Throwable) { return false; }
    }

    private static function compressImage(string $path, string $mime, int $quality): void {
        try {
            $img = match($mime) {
                'image/jpeg' => @imagecreatefromjpeg($path),
                'image/png'  => @imagecreatefrompng($path),
                default      => null,
            };
            if (!$img) return;
            match($mime) {
                'image/jpeg' => imagejpeg($img, $path, $quality),
                'image/png'  => imagepng($img, $path, (int)round((100 - $quality) / 10)),
                default      => null,
            };
            imagedestroy($img);
        } catch (\Throwable) {}
    }

    /** Write public/uploads/.htaccess (no script execution) if it is missing. */
    private static function ensureUploadsRoot(string $root): void {
        if (!is_dir($root) && !mkdir($root, 0755, true) && !is_dir($root)) {
            throw new \RuntimeException('Could not create uploads directory.');
        }
        $htaccess = $root . '/.htaccess';
        if (!is_file($htaccess)) {
            @file_put_contents($htaccess, UploadPolicy::uploadsHtaccess(), LOCK_EX);
        }
    }

    /** Allowed MIME types for external validation checks */
    public static function allowedMimeTypes(): array { return UploadPolicy::allowedMimeTypes(); }
}
