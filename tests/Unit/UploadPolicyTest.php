<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Core\UploadPolicy;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class UploadPolicyTest extends TestCase
{
    public function testExtensionComesFromMimeType(): void
    {
        self::assertSame('jpg', UploadPolicy::extensionFor('image/jpeg'));
        self::assertSame('png', UploadPolicy::extensionFor('image/png'));
        self::assertSame('svg', UploadPolicy::extensionFor('image/svg+xml'));
        self::assertSame('pdf', UploadPolicy::extensionFor('application/pdf'));
        self::assertSame('glb', UploadPolicy::extensionFor('model/gltf-binary'));
    }

    public function testUnknownMimeTypeIsRejected(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('File type not allowed: text/x-php');
        UploadPolicy::extensionFor('text/x-php');
    }

    public function testAllowedMimeTypesListMatchesExtensionMap(): void
    {
        foreach (UploadPolicy::allowedMimeTypes() as $mime) {
            self::assertNotSame('', UploadPolicy::extensionFor($mime));
        }
        self::assertNotContains('text/html', UploadPolicy::allowedMimeTypes());
    }

    public function testRefineMimeRecognisesGlbOnlyWithMagicBytes(): void
    {
        self::assertSame('model/gltf-binary', UploadPolicy::refineMime('application/octet-stream', 'scene.GLB', "glTF\x02\x00\x00\x00"));
        self::assertSame('application/octet-stream', UploadPolicy::refineMime('application/octet-stream', 'scene.glb', 'MZ not a model'));
        self::assertSame('application/octet-stream', UploadPolicy::refineMime('application/octet-stream', 'scene.php', "glTF\x02"));
    }

    public function testRefineMimeMapsXmlWithSvgRootToSvg(): void
    {
        self::assertSame('image/svg+xml', UploadPolicy::refineMime('text/xml', 'logo.svg', '<?xml version="1.0"?><svg xmlns="http://www.w3.org/2000/svg"/>'));
        self::assertSame('text/xml', UploadPolicy::refineMime('text/xml', 'feed.xml', '<?xml version="1.0"?><rss/>'));
    }

    public function testSizeLimits(): void
    {
        UploadPolicy::assertSize(1);
        UploadPolicy::assertSize(UploadPolicy::MAX_BYTES);
        $this->addToAssertionCount(2);

        foreach ([0, UploadPolicy::MAX_BYTES + 1] as $bytes) {
            try {
                UploadPolicy::assertSize($bytes);
                self::fail("Size {$bytes} should be rejected");
            } catch (\RuntimeException) {
                $this->addToAssertionCount(1);
            }
        }
    }

    /** @return iterable<string, array{string, string}> */
    public static function goodFolders(): iterable
    {
        yield 'empty defaults to general' => ['', 'general'];
        yield 'simple' => ['blog', 'blog'];
        yield 'lowercased and trimmed' => ['  Portfolio ', 'portfolio'];
        yield 'nested' => ['blog/2026', 'blog/2026'];
        yield 'surrounding slashes' => ['/projects/', 'projects'];
        yield 'dash and underscore' => ['case-studies_v2', 'case-studies_v2'];
    }

    #[DataProvider('goodFolders')]
    public function testSanitizeFolderAcceptsSafeNames(string $input, string $expected): void
    {
        self::assertSame($expected, UploadPolicy::sanitizeFolder($input));
    }

    /** @return iterable<string, array{string}> */
    public static function badFolders(): iterable
    {
        yield 'parent traversal' => ['../../app'];
        yield 'hidden traversal' => ['blog/../../config'];
        yield 'dot segment' => ['./blog'];
        yield 'backslash' => ['blog\\..\\app'];
        yield 'null byte' => ["blog\0"];
        yield 'space' => ['my folder'];
        yield 'dot in name' => ['shell.php'];
        yield 'double slash' => ['blog//x'];
        yield 'too deep' => ['a/b/c/d'];
        yield 'leading dash' => ['-rf'];
        yield 'too long' => [str_repeat('a', 41)];
    }

    #[DataProvider('badFolders')]
    public function testSanitizeFolderRejectsUnsafeNames(string $input): void
    {
        $this->expectException(\RuntimeException::class);
        UploadPolicy::sanitizeFolder($input);
    }

    public function testCleanOriginalName(): void
    {
        self::assertSame('photo.jpg', UploadPolicy::cleanOriginalName('photo.jpg'));
        self::assertSame('passwd', UploadPolicy::cleanOriginalName('../../etc/passwd'));
        self::assertSame('evil.png', UploadPolicy::cleanOriginalName('C:\\Users\\x\\evil.png'));
        self::assertSame('ab.txt', UploadPolicy::cleanOriginalName("a\r\nb.txt"));
        self::assertSame('file', UploadPolicy::cleanOriginalName('..'));
        self::assertSame('ছবি.png', UploadPolicy::cleanOriginalName('ছবি.png'));

        $long = UploadPolicy::cleanOriginalName(str_repeat('ব', 150) . '.png');
        self::assertLessThanOrEqual(200, strlen($long));
        self::assertTrue(mb_check_encoding($long, 'UTF-8'));
    }

    public function testDimensionLimits(): void
    {
        UploadPolicy::assertDimensions(1, 1);
        UploadPolicy::assertDimensions(6000, 6000);
        $this->addToAssertionCount(2);

        foreach ([[0, 10], [12_001, 10], [10, 12_001], [8000, 8000]] as [$w, $h]) {
            try {
                UploadPolicy::assertDimensions($w, $h);
                self::fail("{$w}x{$h} should be rejected");
            } catch (\RuntimeException) {
                $this->addToAssertionCount(1);
            }
        }
    }

    public function testSafeSvgIsAccepted(): void
    {
        $svg = <<<'SVG'
<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 10 10">
  <defs><linearGradient id="g"><stop offset="0" stop-color="#fff"/></linearGradient></defs>
  <style>.a { fill: url(#g); }</style>
  <rect class="a" width="10" height="10" style="stroke: #000" fill="url(#g)"/>
  <use xlink:href="#g"/>
  <a href="#top"><text>Logo</text></a>
  <image href="data:image/png;base64,iVBORw0KGgo=" width="1" height="1"/>
</svg>
SVG;
        UploadPolicy::assertSafeSvg($svg);
        $this->addToAssertionCount(1);
    }

    /** @return iterable<string, array{string, string}> */
    public static function dangerousSvgs(): iterable
    {
        $open = '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">';
        yield 'script element' => [$open . '<script>alert(1)</script></svg>', '<script>'];
        yield 'namespaced script' => ['<s:svg xmlns:s="http://www.w3.org/2000/svg"><s:script>alert(1)</s:script></s:svg>', '<script>'];
        yield 'onload handler' => ['<svg xmlns="http://www.w3.org/2000/svg" onload="alert(1)"/>', 'event handler'];
        yield 'uppercase handler' => [$open . '<rect ONCLICK="alert(1)"/></svg>', 'event handler'];
        yield 'javascript href' => [$open . '<a href="javascript:alert(1)"><text>x</text></a></svg>', 'in-document'];
        yield 'obfuscated xlink href' => [$open . "<a xlink:href=\" java\tscript:alert(1)\"><text>x</text></a></svg>", 'in-document'];
        yield 'external use' => [$open . '<use href="https://evil.example/x.svg#a"/></svg>', 'in-document'];
        yield 'data html image' => [$open . '<image href="data:text/html;base64,PHNjcmlwdD4="/></svg>', 'in-document'];
        yield 'foreignObject' => [$open . '<foreignObject><div xmlns="http://www.w3.org/1999/xhtml">x</div></foreignObject></svg>', 'foreignObject'];
        yield 'iframe' => [$open . '<iframe src="https://evil.example"/></svg>', 'iframe'];
        yield 'animate to javascript' => [$open . '<a><animate attributeName="href" values="javascript:alert(1)"/></a></svg>', 'script URL'];
        yield 'doctype entity' => ['<!DOCTYPE svg [<!ENTITY x SYSTEM "file:///etc/passwd">]><svg xmlns="http://www.w3.org/2000/svg">&x;</svg>', 'DOCTYPE'];
        yield 'xml stylesheet' => ['<?xml-stylesheet href="https://evil.example/a.css"?><svg xmlns="http://www.w3.org/2000/svg"/>', 'stylesheet'];
        yield 'css import' => [$open . '<style>@import url(https://evil.example/a.css);</style></svg>', 'CSS'];
        yield 'css remote url' => [$open . '<rect style="fill: url(https://evil.example/t.png)"/></svg>', 'CSS'];
        yield 'remote fill url' => [$open . '<rect fill="url(https://evil.example/#g)"/></svg>', 'external url()'];
        yield 'not svg root' => ['<html><body>hi</body></html>', 'root element'];
        yield 'malformed' => ['<svg xmlns="http://www.w3.org/2000/svg"><rect></svg>', 'well-formed'];
        yield 'empty' => ['   ', 'empty'];
    }

    #[DataProvider('dangerousSvgs')]
    public function testDangerousSvgIsRejected(string $svg, string $reason): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage($reason);
        UploadPolicy::assertSafeSvg($svg);
    }

    public function testResolveStoredPathStaysInsideUploads(): void
    {
        self::assertSame('/srv/site/public/uploads/blog/a.jpg', UploadPolicy::resolveStoredPath('/srv/site/public/', '/uploads/blog/a.jpg'));
        self::assertNull(UploadPolicy::resolveStoredPath('/srv/site/public', '/uploads/../index.php'));
        self::assertNull(UploadPolicy::resolveStoredPath('/srv/site/public', '/uploads/./x'));
        self::assertNull(UploadPolicy::resolveStoredPath('/srv/site/public', '/index.php'));
        self::assertNull(UploadPolicy::resolveStoredPath('/srv/site/public', '/assets/css/app.css'));
        self::assertNull(UploadPolicy::resolveStoredPath('/srv/site/public', ''));
        self::assertNull(UploadPolicy::resolveStoredPath('/srv/site/public', "/uploads/a\0.php"));
        self::assertNull(UploadPolicy::resolveStoredPath('/srv/site/public', '/uploads\\..\\.env'));
    }

    public function testUploadsHtaccessDisablesScripts(): void
    {
        $rules = UploadPolicy::uploadsHtaccess();
        self::assertStringContainsString('php_flag engine off', $rules);
        self::assertStringContainsString('RemoveHandler .php', $rules);
        self::assertStringContainsString('Require all denied', $rules);
        self::assertStringContainsString('nosniff', $rules);
        self::assertStringContainsString('sandbox', $rules);
    }
}
