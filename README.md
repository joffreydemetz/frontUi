# jdz/frontUi

Framework-agnostic front UI helpers for simple Slim/Twig sites.

The front-end counterpart to [`jdz/adminUi`](https://jdz.joffreydemetz.com/adminui):
a small, dependency-light Twig extension for sites that ship the
`jizy-front.js` / `jizy-front.css` bundle on a plain Slim/Twig front.

## Installation

```bash
composer require jdz/frontui
```

## Requirements

- PHP >= 8.2
- [`jdz/ui`](https://jdz.joffreydemetz.com/ui) ^1.0 — the shared HTML layer (image tag, content cleaning, attributes)
- `twig/twig` ^3.0

The optional thumbnail mode of `jizyImg()` generates thumbnails with GD.

## Contents

| Namespace | Class | Role |
|---|---|---|
| `JDZ\FrontUi\Twig` | `FrontUiExtension` | Registers the `asset()` and `jizyImg()` Twig functions |
| `JDZ\FrontUi\Html` | `Image`, `Helper`, `Attributes` | Backward-compatibility aliases (since 1.3.0) of `JDZ\Ui\Html\Image`, `Helper` and `Attributes` from [`jdz/ui`](https://jdz.joffreydemetz.com/ui) — new code should use the `jdz/ui` classes directly |

What the aliased classes do (implemented in `jdz/ui`, which also ships the
`Sanitizer`):

- `Image` builds a native-lazy, picviewer-ready `<img>` tag.
- `Helper` sanitises / normalises WYSIWYG content for front display (`clean()`,
  `cleanDom()`); `cleanBlocks()` / `cleanLinks()` hooks let a subclass inject
  its own transforms.
- `Attributes` parses / merges HTML tag attribute strings.

## Scope

Rendering helpers only — no configuration object, no asset package, no
framework kernel.

- `asset(file, versionable = true)` — root-absolute URL with an optional
  cache-busting `?v=…` query.
- `jizyImg(src, alt, zoom = false, width = 0)` — `<img loading="lazy">` with
  width/height of the file served (jdz/ui ≥ 1.2); `zoom` adds the `data-zoom`
  attribute the bundled `Modalizer.picviewer` reads to open a gallery layer;
  `width > 0` serves an on-demand cached thumbnail (`src` = thumb, `data-src` =
  original for lozad) — the attributes are then the thumb's.

Without a `width`, a simple front site serves the original asset with native
lazy-loading — thumbnails are opt-in per call.

## Usage

```php
use JDZ\FrontUi\Twig\FrontUiExtension;
use Slim\Views\Twig;

$twig = Twig::create(__DIR__ . '/templates');
$twig->addExtension(new FrontUiExtension(
    __DIR__ . '/public',   // web root, for image dimensions
    '160',                 // asset cache-busting version
    // optional: thumbsDir = 'thumbs' (thumbnail cache under the web root),
    //           cacheLife = 0 (seconds before a thumbnail is rebuilt; 0 = never),
    //           fallbackSrc = '' (placeholder served when a source file is missing)
));
```

```twig
<link rel="stylesheet" href="{{ asset('assets/css/theme.min.css') }}" />
<figure>{{ jizyImg('media/pages/photo.jpg', 'Légende', true) }}</figure>
```

```php
use JDZ\Ui\Html\Helper;                     // JDZ\FrontUi\Html\Helper still works (alias)

$clean = Helper::clean($redactorContent);   // tag whitelist + FR typography
$tidy  = Helper::cleanDom($clean);          // strip inline styles + empty nodes
```

## Changelog

- **1.4.0** — README wording only.
- **1.3.0** — The Html layer (`Image`, `Helper`, `Attributes`) moved to `jdz/ui`; `JDZ\FrontUi\Html\*` kept as aliases.
- **1.2.0** — Image fallback (`fallbackSrc`).
- **1.1.0** — Opt-in on-demand thumbnail mode (`jizyImg` width).
- **1.0.0** — Initial release.

## License

MIT — see [LICENSE](LICENSE).
