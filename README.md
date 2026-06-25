# jdz/frontUi

Framework-agnostic front UI helpers for simple Slim/Twig sites.

The front-end counterpart to [`jdz/adminUi`](https://jdz.joffreydemetz.com/adminui):
small, dependency-light Twig helpers shared by sites that ship the
`jizy-front.js` / `jizy-front.css` bundle without the Callisto framework
runtime — jupiter's own properties and the saturn sites migrated to a plain
Slim front.

## Contents

| Namespace | Class | Role |
|---|---|---|
| `JDZ\FrontUi\Twig` | `FrontUiExtension` | Registers the `asset()` and `jizyImg()` Twig functions |
| `JDZ\FrontUi\Html` | `Image` | Builds a native-lazy, picviewer-ready `<img>` tag |
| `JDZ\FrontUi\Html` | `Helper` | Sanitises / normalises WYSIWYG content for front display (`clean()`, `cleanDom()`); `cleanBlocks()` / `cleanLinks()` hooks let a framework subclass inject its own transforms |
| `JDZ\FrontUi\Html` | `Attributes` | Parse / merge HTML tag attribute strings |

## Scope

Rendering helpers only. This is the **framework-free** replacement for the
framework's `JizyTwigExtension`, which is coupled to the Callisto kernel
(`Config`, Symfony `Asset\Package`, the GD/Imagine thumbnail pipeline).

- `asset(file, versionable = true)` — root-absolute URL with an optional
  cache-busting `?v=…` query.
- `jizyImg(src, alt, zoom = false)` — `<img loading="lazy">` with width/height
  read from the source file; `zoom` adds the `data-zoom` attribute the bundled
  `Modalizer.picviewer` reads to open a gallery layer.

Thumbnail generation is intentionally out of scope — a simple front site serves
the original asset with native lazy-loading.

## Usage

```php
use JDZ\FrontUi\Twig\FrontUiExtension;
use Slim\Views\Twig;

$twig = Twig::create(__DIR__ . '/templates');
$twig->addExtension(new FrontUiExtension(
    __DIR__ . '/public',   // web root, for image dimensions
    '160',                 // asset cache-busting version
));
```

```twig
<link rel="stylesheet" href="{{ asset('assets/css/theme.min.css') }}" />
<figure>{{ jizyImg('media/pages/photo.jpg', 'Légende', true) }}</figure>
```

```php
use JDZ\FrontUi\Html\Helper;

$clean = Helper::clean($redactorContent);   // tag whitelist + FR typography
$tidy  = Helper::cleanDom($clean);          // strip inline styles + empty nodes
```

## Requirements

PHP >= 8.2, `twig/twig` ^3.0.
