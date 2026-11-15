<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Core\Upload;
use PHPUnit\Framework\TestCase;

/**
 * Upload::inspect() on real temporary files: content sniffing, extension
 * choice and image/SVG checks, without moving anything into public/.
 */
final class UploadInspectTest extends TestCase
{
    /** @var list<string> */
    private array $files = [];

    protected function tearDown(): void
    {
        foreach ($this->files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    private function tmp(string $contents): string
    {
        $path = tempnam(sys_get_temp_dir(), 'upload-test-');
        self::assertIsString($path);
        file_put_contents($path, $contents);
        $this->files[] = $path;
        return $path;
    }

    /** Minimal PNG with the given IHDR dimensions (pixel data is not needed by getimagesize). */
    private static function png(int $width, int $height): string
    {
        $chunk = static function (string $type, string $data): string {
            return pack('N', strlen($data)) . $type . $data . pack('N', crc32($type . $data));
        };
        $ihdr = pack('NN', $width, $height) . "\x08\x02\x00\x00\x00";
        $idat = (string) gzcompress("\x00\x00\x00\x00");
        return "\x89PNG\r\n\x1a\n" . $chunk('IHDR', $ihdr) . $chunk('IDAT', $idat) . $chunk('IEND', '');
    }

    public function testPngIsAcceptedWithDimensions(): void
    {
        $bytes = self::png(1, 1);
        $info = Upload::inspect($this->tmp($bytes), 'avatar.png', strlen($bytes));

        self::assertSame('image/png', $info['mime']);
        self::assertSame('png', $info['ext']);
        self::assertSame(1, $info['width']);
        self::assertSame(1, $info['height']);
    }

    public function testClientExtensionIsIgnoredForPolyglot(): void
    {
        // PNG magic bytes followed by PHP code, uploaded as "shell.php".
        $bytes = self::png(2, 3) . '<?php system($_GET["c"]); ?>';
        $info = Upload::inspect($this->tmp($bytes), 'shell.php', strlen($bytes));

        self::assertSame('png', $info['ext'], 'stored extension must follow the sniffed type');
    }

    public function testPlainPhpFileIsRejected(): void
    {
        $bytes = "<?php echo 'hi';\n";
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('File type not allowed');
        Upload::inspect($this->tmp($bytes), 'photo.jpg', strlen($bytes));
    }

    public function testHtmlDisguisedAsImageIsRejected(): void
    {
        $bytes = "<!DOCTYPE html><html><body><script>alert(1)</script></body></html>\n";
        $this->expectException(\RuntimeException::class);
        Upload::inspect($this->tmp($bytes), 'image.png', strlen($bytes));
    }

    public function testDecompressionBombIsRejected(): void
    {
        $bytes = self::png(50_000, 50_000);
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('too large');
        Upload::inspect($this->tmp($bytes), 'huge.png', strlen($bytes));
    }

    public function testSafeSvgIsAccepted(): void
    {
        $bytes = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1 1"><rect width="1" height="1"/></svg>';
        $info = Upload::inspect($this->tmp($bytes), 'logo.svg', strlen($bytes));

        self::assertSame('image/svg+xml', $info['mime']);
        self::assertSame('svg', $info['ext']);
        self::assertNull($info['width']);
    }

    public function testScriptedSvgIsRejected(): void
    {
        $bytes = '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(document.cookie)</script></svg>';
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('SVG rejected');
        Upload::inspect($this->tmp($bytes), 'logo.svg', strlen($bytes));
    }

    public function testGlbIsRecognisedByMagicBytes(): void
    {
        $bytes = "glTF\x02\x00\x00\x00" . pack('V', 20) . str_repeat("\x00", 8);
        $info = Upload::inspect($this->tmp($bytes), 'model.glb', strlen($bytes));

        self::assertSame('model/gltf-binary', $info['mime']);
        self::assertSame('glb', $info['ext']);
    }

    public function testEmptyFileIsRejected(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Empty file');
        Upload::inspect($this->tmp(''), 'empty.png', 0);
    }
}
