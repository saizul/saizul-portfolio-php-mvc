<?php declare(strict_types=1);
namespace App\Core;

/**
 * Upload — handles single and bulk file uploads.
 * Converts images to WebP (if GD available), compresses, validates.
 */
class Upload {

    private static array $allowedMime = [
        'image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml',
        'application/pdf', 'model/gltf-binary',
    ];

    private static array $imgMime = ['image/jpeg','image/png','image/gif','image/webp'];

    private static int $maxBytes = 10_485_760; // 10 MB

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
            if ($file['error'] !== UPLOAD_ERR_OK) continue;
            try {
                $results[] = self::processFile($file, $folder, $toWebP);
            } catch (\Throwable $e) {
                // Skip invalid files silently; caller can check count
            }
        }
        return $results;
    }

    private static function processFile(array $file, string $folder, bool $toWebP): array {
        // Size check
        if ($file['size'] > self::$maxBytes) {
            throw new \RuntimeException('File too large (max 10 MB).');
        }

        // MIME validation (finfo takes precedence over browser-reported type)
        $finfo    = new \finfo(FILEINFO_MIME_TYPE);
        $realMime = $finfo->file($file['tmp_name']);
        if (!in_array($realMime, self::$allowedMime, true)) {
            throw new \RuntimeException("File type not allowed: {$realMime}");
        }

        // Build upload path
        $uploadDir = BASE_PATH . "/public/uploads/{$folder}";
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $safeName = bin2hex(random_bytes(8)) . '.' . $ext;
        $destPath = "{$uploadDir}/{$safeName}";
        $webPath  = "/uploads/{$folder}/{$safeName}";

        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            throw new \RuntimeException('Could not move uploaded file.');
        }

        $result = [
            'path'      => $webPath,
            'webp_path' => null,
            'width'     => null,
            'height'    => null,
            'mime'      => $realMime,
            'size'      => $file['size'],
            'filename'  => $file['name'],
            'folder'    => $folder,
        ];

        // Image processing
        if (in_array($realMime, self::$imgMime, true) && function_exists('imagecreatefromjpeg')) {
            $dims = @getimagesize($destPath);
            if ($dims) {
                $result['width']  = $dims[0];
                $result['height'] = $dims[1];
            }

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
            if ($file['size'] > 500_000 && $realMime !== 'image/webp') {
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

    /** Allowed MIME types for external validation checks */
    public static function allowedMimeTypes(): array { return self::$allowedMime; }
}
