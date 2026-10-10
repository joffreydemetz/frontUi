<?php

declare(strict_types=1);

namespace JDZ\FrontUi\Tests\Twig;

use JDZ\FrontUi\Twig\FrontUiExtension;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

/**
 * The two Twig functions as a site template calls them. The image markup itself
 * belongs to jdz/ui (Html\Image) and is tested there: here only what the
 * extension adds (registration, raw output, the public path it hands over).
 */
class FrontUiExtensionTest extends TestCase
{
    private ?string $public = null;

    protected function tearDown(): void
    {
        if (null !== $this->public) {
            foreach (glob($this->public . '/img/*') ?: [] as $file) {
                unlink($file);
            }
            @rmdir($this->public . '/img');
            rmdir($this->public);
        }
    }

    /** a public dir holding img/wide.png, 4 x 2 px */
    private function publicDir(): string
    {
        $this->public = sys_get_temp_dir() . '/jdz-frontui-' . bin2hex(random_bytes(6));
        mkdir($this->public . '/img', 0777, true);
        imagepng(imagecreatetruecolor(4, 2), $this->public . '/img/wide.png');

        return $this->public;
    }

    private static function render(FrontUiExtension $extension, string $template): string
    {
        $twig = new Environment(new ArrayLoader(['page' => $template]), ['autoescape' => 'html']);
        $twig->addExtension($extension);

        return $twig->render('page');
    }

    public static function assets(): array
    {
        return [
            'versioned' => ['css/site.css', true, '20261010', '/css/site.css?v=20261010'],
            'a leading slash is not doubled' => ['/css/site.css', true, '20261010', '/css/site.css?v=20261010'],
            'an existing query string' => ['js/app.js?mode=dark', true, '7', '/js/app.js?mode=dark&v=7'],
            'not versionable' => ['img/logo.svg', false, '20261010', '/img/logo.svg'],
            'no version configured' => ['css/site.css', true, '', '/css/site.css'],
        ];
    }

    #[DataProvider('assets')]
    public function testAssetBuildsTheRootAbsoluteUrl(string $file, bool $versionable, string $version, string $url): void
    {
        $this->assertSame($url, (new FrontUiExtension('/var/www/public', $version))->asset($file, $versionable));
    }

    public function testTheTemplateFunctionsAreRegistered(): void
    {
        $html = self::render(
            new FrontUiExtension($this->publicDir() . '/', '3'),
            '{{ asset("css/site.css") }}|{{ jizyImg("img/wide.png", "Wide", true) }}'
        );

        // jizyImg is HTML-safe: its <img> is not escaped by the template
        $this->assertSame(
            '/css/site.css?v=3|<img src="/img/wide.png" alt="Wide" width="4" height="2" loading="lazy" data-orientation="landscape" data-zoom="img/wide.png" />',
            $html
        );
    }

    public function testAMissingImageGetsTheFallback(): void
    {
        $extension = new FrontUiExtension($this->publicDir(), '', 'thumbs', 0, '/img/missing-fallback.png');

        $this->assertSame(
            '<img src="/img/missing-fallback.png" alt="Gone" loading="lazy" />',
            $extension->jizyImg('img/gone.png', 'Gone')
        );
    }
}
