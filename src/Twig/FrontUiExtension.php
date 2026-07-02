<?php

declare(strict_types=1);

namespace JDZ\FrontUi\Twig;

use JDZ\FrontUi\Html\Image;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Twig helpers shared by simple Slim/Twig front sites:
 *
 *   asset(file, versionable = true)          → versioned URL under the web root
 *   jizyImg(src, alt, zoom = false, width=0) → native lazy <img>, picviewer-ready;
 *                                              width>0 → on-demand cached thumbnail
 *                                              (src=thumb + data-src=original for lozad)
 *
 * Framework-free replacement for the framework's JizyTwigExtension. Register it on
 * the site's Twig environment with the public path, a cache-busting version string,
 * and (optionally) the thumbnail cache dir + cache-life.
 *
 * @author Joffrey Demetz <joffrey.demetz@gmail.com>
 */
class FrontUiExtension extends AbstractExtension
{
    private Image $image;

    public function __construct(
        private string $publicPath,
        private string $assetVersion = '',
        string $thumbsDir = 'thumbs',
        int $cacheLife = 0,
        string $fallbackSrc = '',
    ) {
        $this->publicPath = rtrim($publicPath, '/\\');
        $this->image = new Image($this->publicPath, $thumbsDir, $cacheLife, $fallbackSrc);
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('asset', [$this, 'asset']),
            new TwigFunction('jizyImg', [$this, 'jizyImg'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * Root-absolute URL for a file under the web root, with an optional
     * cache-busting ?v=… query.
     */
    public function asset(string $file, bool $versionable = true): string
    {
        $url = '/' . ltrim($file, '/');

        if ($versionable && '' !== $this->assetVersion) {
            $url .= (str_contains($url, '?') ? '&' : '?') . 'v=' . $this->assetVersion;
        }

        return $url;
    }

    /**
     * Native lazy <img>. When $zoom is true the picviewer gallery attribute is
     * added. When $width > 0 an on-demand thumbnail is generated/served.
     */
    public function jizyImg(string $src, string $alt = '', bool $zoom = false, int $width = 0): string
    {
        return $this->image->render($src, $alt, $zoom, $width);
    }
}
