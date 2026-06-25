<?php

declare(strict_types=1);

namespace JDZ\FrontUi\Html;

/**
 * Framework-agnostic <img> tag builder.
 *
 * Emits a native lazy-loaded image. When zoom is requested it adds the
 * `data-zoom` attribute the jizy-front picviewer reads to open a gallery
 * layer. Width / height / orientation are read from the source file (when it
 * is resolvable on disk) so the browser can reserve layout space.
 *
 * No thumbnailing, no GD, no Imagine — that pipeline lives in the framework.
 * A simple front site serves the original asset with `loading="lazy"`.
 *
 * @author Joffrey Demetz <joffrey.demetz@gmail.com>
 */
class Image
{
    private string $publicPath;

    public function __construct(string $publicPath)
    {
        $this->publicPath = rtrim($publicPath, '/\\');
    }

    public function render(string $src, string $alt = '', bool $zoom = false): string
    {
        $src = ltrim($src, '/');
        $full = $this->publicPath . '/' . $src;

        $attrs = [
            'src' => '/' . $src,
            'alt' => trim(str_replace('"', '', $alt)),
            'loading' => 'lazy',
        ];

        if (is_file($full) && false !== ($size = @getimagesize($full))) {
            [$w, $h] = $size;
            // No width/height attributes — images are sized to their container
            // by CSS; forcing intrinsic dimensions would override that. Keep only
            // the orientation hint the theme uses for portrait/landscape styling.
            $attrs['data-orientation'] = $w < $h ? 'portrait' : 'landscape';
        }

        if ($zoom) {
            $attrs['data-zoom'] = $src;
        }

        $html = '';
        foreach ($attrs as $key => $value) {
            $html .= ' ' . $key . '="' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '"';
        }

        return '<img' . $html . ' />';
    }
}
