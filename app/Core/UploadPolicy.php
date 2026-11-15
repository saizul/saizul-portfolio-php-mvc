<?php declare(strict_types=1);
namespace App\Core;

/**
 * UploadPolicy — pure validation rules for media uploads.
 *
 * Everything here works on strings and numbers only (no $_FILES, no
 * filesystem writes), so the rules can be unit-tested. App\Core\Upload
 * applies them before a file is moved into public/uploads/.
 *
 * Rules:
 *  - the stored extension is derived from the sniffed MIME type, never from
 *    the client's filename (so "shell.php" with JPEG magic bytes is stored
 *    as ".jpg");
 *  - folder names are restricted to lowercase [a-z0-9_-] segments;
 *  - SVG files are parsed and rejected if they contain scripts, event
 *    handlers, external references, DOCTYPE/entities or foreignObject;
 *  - raster images are capped by width, height and total pixels to stop
 *    decompression bombs from exhausting GD memory.
 */
final class UploadPolicy {

    public const MAX_BYTES = 10_485_760; // 10 MB

    public const MAX_SIDE   = 12_000;     // px, either dimension
    public const MAX_PIXELS = 40_000_000; // 40 megapixels

    public const MAX_FOLDER_DEPTH = 3;

    /** Allowed MIME type => canonical stored extension. */
    private const EXTENSIONS = [
        'image/jpeg'        => 'jpg',
        'image/png'         => 'png',
        'image/gif'         => 'gif',
        'image/webp'        => 'webp',
        'image/svg+xml'     => 'svg',
        'application/pdf'   => 'pdf',
        'model/gltf-binary' => 'glb',
    ];

    /** Raster types handled by GD. */
    private const RASTER = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

    /** SVG elements that can run script, embed documents or load remote content. */
    private const SVG_FORBIDDEN_ELEMENTS = [
        'script', 'foreignobject', 'iframe', 'embed', 'object', 'audio',
        'video', 'handler', 'listener', 'link', 'meta', 'base',
    ];

    /** @return list<string> */
    public static function allowedMimeTypes(): array {
        return array_keys(self::EXTENSIONS);
    }

    public static function isRaster(string $mime): bool {
        return in_array($mime, self::RASTER, true);
    }

    /**
     * Canonical extension for an allowed MIME type.
     *
     * @throws \RuntimeException when the type is not allowed
     */
    public static function extensionFor(string $mime): string {
        if (!isset(self::EXTENSIONS[$mime])) {
            throw new \RuntimeException("File type not allowed: {$mime}");
        }
        return self::EXTENSIONS[$mime];
    }

    /**
     * finfo reports some files with a generic type; map those to the real one
     * using the client extension only as a hint, never as the stored name.
     * Currently: .glb files are often reported as application/octet-stream,
     * and are accepted only if they start with the glTF binary magic "glTF".
     */
    public static function refineMime(string $sniffed, string $clientName, string $head): string {
        $ext = strtolower(pathinfo($clientName, PATHINFO_EXTENSION));
        if ($sniffed === 'application/octet-stream' && $ext === 'glb' && strncmp($head, 'glTF', 4) === 0) {
            return 'model/gltf-binary';
        }
        if ($sniffed === 'text/xml' || $sniffed === 'application/xml' || $sniffed === 'image/svg') {
            if (stripos($head, '<svg') !== false) {
                return 'image/svg+xml';
            }
        }
        return $sniffed;
    }

    /**
     * @throws \RuntimeException when the size is zero or above the limit
     */
    public static function assertSize(int $bytes): void {
        if ($bytes <= 0) {
            throw new \RuntimeException('Empty file.');
        }
        if ($bytes > self::MAX_BYTES) {
            throw new \RuntimeException('File too large (max 10 MB).');
        }
    }

    /**
     * Normalise an admin-supplied media folder name.
     * "Blog/2026" => "blog/2026"; "" => "general".
     *
     * @throws \RuntimeException for traversal, absolute paths or odd characters
     */
    public static function sanitizeFolder(string $folder): string {
        if (str_contains($folder, '\\') || str_contains($folder, "\0")) {
            throw new \RuntimeException('Invalid upload folder.');
        }
        $folder = strtolower(trim($folder, " \t"));
        if ($folder === '') {
            return 'general';
        }
        $segments = explode('/', trim($folder, '/'));
        if (count($segments) > self::MAX_FOLDER_DEPTH) {
            throw new \RuntimeException('Upload folder is nested too deeply.');
        }
        foreach ($segments as $segment) {
            if (!preg_match('/^[a-z0-9][a-z0-9_-]{0,39}$/', $segment)) {
                throw new \RuntimeException('Invalid upload folder.');
            }
        }
        return implode('/', $segments);
    }

    /**
     * Display name kept in the database: basename only, no control
     * characters, at most 200 bytes, never empty.
     */
    public static function cleanOriginalName(string $name): string {
        $name = str_replace('\\', '/', $name);
        $name = basename($name);
        $name = (string) preg_replace('/[\x00-\x1F\x7F]+/u', '', $name);
        $name = trim($name, " .");
        if ($name === '') {
            return 'file';
        }
        if (strlen($name) > 200) {
            $name = mb_strcut($name, 0, 200, 'UTF-8');
        }
        return $name;
    }

    /**
     * @throws \RuntimeException when an image is larger than the limits
     */
    public static function assertDimensions(int $width, int $height): void {
        if ($width <= 0 || $height <= 0) {
            throw new \RuntimeException('Could not read image dimensions.');
        }
        if ($width > self::MAX_SIDE || $height > self::MAX_SIDE) {
            throw new \RuntimeException('Image is too large (max ' . self::MAX_SIDE . ' px per side).');
        }
        if ($width * $height > self::MAX_PIXELS) {
            throw new \RuntimeException('Image has too many pixels (max 40 megapixels).');
        }
    }

    /**
     * Reject SVG documents that could execute script when opened directly
     * from /uploads/ (SVG is an active document, not a plain image).
     *
     * @throws \RuntimeException with the first problem found
     */
    public static function assertSafeSvg(string $svg): void {
        if (trim($svg) === '') {
            throw new \RuntimeException('SVG rejected: empty document.');
        }
        if (preg_match('/<!DOCTYPE|<!ENTITY/i', $svg)) {
            throw new \RuntimeException('SVG rejected: DOCTYPE and entity declarations are not allowed.');
        }
        if (preg_match('/<\?xml-stylesheet/i', $svg)) {
            throw new \RuntimeException('SVG rejected: external stylesheets are not allowed.');
        }

        $doc = new \DOMDocument();
        $previous = libxml_use_internal_errors(true);
        $loaded = $doc->loadXML($svg, LIBXML_NONET | LIBXML_NOBLANKS);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if (!$loaded || $doc->documentElement === null) {
            throw new \RuntimeException('SVG rejected: not well-formed XML.');
        }
        if (strtolower($doc->documentElement->localName) !== 'svg') {
            throw new \RuntimeException('SVG rejected: root element must be <svg>.');
        }

        $xpath = new \DOMXPath($doc);
        foreach ($xpath->query('//*') ?: [] as $el) {
            /** @var \DOMElement $el */
            $tag = strtolower($el->localName);
            if (in_array($tag, self::SVG_FORBIDDEN_ELEMENTS, true)) {
                throw new \RuntimeException("SVG rejected: <{$el->localName}> is not allowed.");
            }
            if ($tag === 'style' && self::isDangerousCss($el->textContent)) {
                throw new \RuntimeException('SVG rejected: unsafe CSS in <style>.');
            }
            foreach ($el->attributes as $attr) {
                /** @var \DOMAttr $attr */
                self::assertSafeSvgAttribute($tag, strtolower($attr->localName), $attr->value);
            }
        }
        $instructions = $xpath->query('//processing-instruction()');
        if ($instructions !== false && $instructions->length > 0) {
            throw new \RuntimeException('SVG rejected: processing instructions are not allowed.');
        }
    }

    private static function assertSafeSvgAttribute(string $tag, string $name, string $value): void {
        if (str_starts_with($name, 'on')) {
            throw new \RuntimeException("SVG rejected: event handler attribute \"{$name}\" is not allowed.");
        }
        $compact = strtolower((string) preg_replace('/[\s\x00-\x1F]+/', '', $value));

        if ($name === 'href') { // covers href and xlink:href (localName)
            if ($compact === '' || str_starts_with($compact, '#')) {
                return;
            }
            if ($tag === 'image' && preg_match('#^data:image/(png|jpeg|gif|webp);base64,#', $compact)) {
                return;
            }
            throw new \RuntimeException('SVG rejected: only in-document (#id) references are allowed.');
        }

        if ($name === 'style' && self::isDangerousCss($value)) {
            throw new \RuntimeException('SVG rejected: unsafe CSS in style attribute.');
        }

        if (str_contains($compact, 'javascript:') || str_contains($compact, 'vbscript:')) {
            throw new \RuntimeException("SVG rejected: script URL in \"{$name}\".");
        }

        // Presentation attributes such as fill="url(...)" may only point inside the document.
        if (preg_match_all('/url\(([^)]*)\)/', $compact, $m)) {
            foreach ($m[1] as $target) {
                if (!str_starts_with(trim($target, "'\""), '#')) {
                    throw new \RuntimeException("SVG rejected: external url() in \"{$name}\".");
                }
            }
        }
    }

    private static function isDangerousCss(string $css): bool {
        $compact = strtolower((string) preg_replace('/[\s\x00-\x1F]+/', '', $css));
        if (str_contains($compact, '@import') || str_contains($compact, 'expression(')
            || str_contains($compact, 'javascript:') || str_contains($compact, '-moz-binding')) {
            return true;
        }
        if (preg_match_all('/url\(([^)]*)\)/', $compact, $m)) {
            foreach ($m[1] as $target) {
                if (!str_starts_with(trim($target, "'\""), '#')) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Map a web path stored in the media table ("/uploads/blog/x.jpg") to an
     * absolute file path, or null if it points outside public/uploads/.
     */
    public static function resolveStoredPath(string $publicDir, string $webPath): ?string {
        if ($webPath === '' || str_contains($webPath, "\0") || str_contains($webPath, '\\')) {
            return null;
        }
        if (!str_starts_with($webPath, '/uploads/')) {
            return null;
        }
        foreach (explode('/', $webPath) as $segment) {
            if ($segment === '..' || $segment === '.') {
                return null;
            }
        }
        return rtrim($publicDir, '/') . $webPath;
    }

    /**
     * Contents of public/uploads/.htaccess: serve files as static data only.
     * Written by Upload the first time the uploads root is created.
     */
    public static function uploadsHtaccess(): string {
        return <<<'HTACCESS'
# Written by App\Core\UploadPolicy. Uploaded files are data, never code.
Options -Indexes -ExecCGI
<IfModule mod_php.c>
    php_flag engine off
</IfModule>
<IfModule mod_php7.c>
    php_flag engine off
</IfModule>
RemoveHandler .php .phtml .php3 .php4 .php5 .php7 .php8 .phar .pht .cgi .pl .py .shtml
RemoveType .php .phtml .php3 .php4 .php5 .php7 .php8 .phar .pht
<FilesMatch "(?i)\.(php\d?|phtml|phar|pht|cgi|pl|py|sh|shtml|htaccess|htpasswd)$">
    Require all denied
</FilesMatch>
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options "nosniff"
    <FilesMatch "(?i)\.svg$">
        Header always set Content-Security-Policy "default-src 'none'; style-src 'unsafe-inline'; img-src data:; sandbox"
    </FilesMatch>
</IfModule>

HTACCESS;
    }
}
