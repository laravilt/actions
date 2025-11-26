![Actions](./arts/screenshot.jpg)

# Actions Plugin for Laravilt

[![Latest Stable Version](https://poser.pugx.org/laravilt/actions/version.svg)](https://packagist.org/packages/laravilt/actions)
[![License](https://poser.pugx.org/laravilt/actions/license.svg)](https://packagist.org/packages/laravilt/actions)
[![Downloads](https://poser.pugx.org/laravilt/actions/d/total.svg)](https://packagist.org/packages/laravilt/actions)
[![Dependabot Updates](https://github.com/laravilt/actions/actions/workflows/dependabot/dependabot-updates/badge.svg)](https://github.com/laravilt/actions/actions/workflows/dependabot/dependabot-updates)
[![PHP Code Styling](https://github.com/laravilt/actions/actions/workflows/fix-php-code-styling.yml/badge.svg)](https://github.com/laravilt/actions/actions/workflows/fix-php-code-styling.yml)
[![Tests](https://github.com/laravilt/actions/actions/workflows/tests.yml/badge.svg)](https://github.com/laravilt/actions/actions/workflows/tests.yml)

Pre-built, fully customizable components for Inertia.js, seamlessly integrated with a PHP backend and Laravel framework. This package is optimized for high-end performance and compatibility with FilamentPHP v4, providing a powerful solution for building modern, reactive web applications.

## Installation

You can install the plugin via composer:

```bash
composer require laravilt/actions
```

The package will automatically register its service provider which handles all Laravel-specific functionality (views, migrations, config, etc.).

## Usage

Register the plugin in your Filament panel provider:

```php
use Laravilt\Actions\ActionsPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        // ...
        ->plugin(new ActionsPlugin());
}
```
## Configuration

Publish the config file:

```bash
php artisan vendor:publish --tag="actions-config"
```

## Assets

Publish the plugin assets:

```bash
php artisan vendor:publish --tag="actions-assets"
```

## Testing

```bash
composer test
```

## Code Style

```bash
composer format
```

## Static Analysis

```bash
composer analyse
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
