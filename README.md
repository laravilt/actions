![Actions](https://raw.githubusercontent.com/laravilt/actions/master/arts/screenshot.jpg)

# Laravilt Actions

[![Latest Stable Version](https://poser.pugx.org/laravilt/actions/version.svg)](https://packagist.org/packages/laravilt/actions)
[![License](https://poser.pugx.org/laravilt/actions/license.svg)](https://packagist.org/packages/laravilt/actions)
[![Downloads](https://poser.pugx.org/laravilt/actions/d/total.svg)](https://packagist.org/packages/laravilt/actions)
[![Dependabot Updates](https://github.com/laravilt/actions/actions/workflows/dependabot/dependabot-updates/badge.svg)](https://github.com/laravilt/actions/actions/workflows/dependabot/dependabot-updates)
[![PHP Code Styling](https://github.com/laravilt/actions/actions/workflows/fix-php-code-styling.yml/badge.svg)](https://github.com/laravilt/actions/actions/workflows/fix-php-code-styling.yml)
[![Tests](https://github.com/laravilt/actions/actions/workflows/tests.yml/badge.svg)](https://github.com/laravilt/actions/actions/workflows/tests.yml)

Complete action system with modal support, authorization, and Inertia.js integration for Laravilt. Build interactive UI components with buttons, links, and icon buttons. Includes confirmation modals, custom forms, password protection, and secure token-based execution.

## Features

- 🎨 **Multiple Variants** - Button, link, and icon button styles
- 🔒 **Authorization** - Closure-based authorization with record-level checks
- 📊 **Modal Support** - Confirmation modals, custom forms, slide-overs
- 🎯 **Flexible Configuration** - Colors, icons, sizes, tooltips
- 🔗 **URL Handling** - External URLs, internal actions, new tab support
- ⚡ **Inertia Integration** - Seamless Vue 3 integration
- 📤 **Export/Import** - Excel/CSV export and import with Laravel Excel
- 🔄 **Soft Delete Support** - Built-in restore and force delete actions

## Action Types

| Type | Description |
|------|-------------|
| `Action` | Standard action button |
| `BulkAction` | Action for multiple selected records |
| `ViewAction` | Navigate to view page |
| `EditAction` | Navigate to edit page |
| `DeleteAction` | Soft delete record |
| `CreateAction` | Navigate to create page |
| `ReplicateAction` | Duplicate a record |
| `RestoreAction` | Restore soft-deleted record |
| `ForceDeleteAction` | Permanently delete record |
| `ExportAction` | Export data to Excel/CSV |
| `ImportAction` | Import data from Excel/CSV |
| `DeleteBulkAction` | Bulk soft delete |
| `RestoreBulkAction` | Bulk restore |
| `ForceDeleteBulkAction` | Bulk permanent delete |

## Colors

```php
->color('primary')  // Blue
->color('secondary') // Gray
->color('success')   // Green
->color('danger')    // Red
->color('warning')   // Yellow
->color('info')      // Light blue
```

## Modal Types

```php
// Confirmation modal
->requiresConfirmation()
->modalHeading('Delete User')
->modalDescription('Are you sure?')

// Form modal
->schema([
    TextInput::make('reason')->required(),
])

// Slide-over
->slideOver()
```

## Installation

```bash
composer require laravilt/actions
```

The package will automatically register its service provider.

## Quick Start

```php
use Laravilt\Actions\Action;

$action = Action::make('delete')
    ->label('Delete')
    ->icon('trash-2')
    ->color('danger')
    ->requiresConfirmation()
    ->modalHeading('Delete User')
    ->modalDescription('Are you sure?')
    ->action(function ($record) {
        $record->delete();
    });
```

## Export & Import

```php
use Laravilt\Actions\ExportAction;
use Laravilt\Actions\ImportAction;

// Export with custom exporter class
ExportAction::make()
    ->exporter(UserExporter::class)
    ->fileName('users.xlsx');

// Import with custom importer class
ImportAction::make()
    ->importer(UserImporter::class);
```

## Soft Delete Actions

```php
use Laravilt\Actions\DeleteAction;
use Laravilt\Actions\RestoreAction;
use Laravilt\Actions\ForceDeleteAction;

// Auto-hidden for trashed records
DeleteAction::make();

// Auto-visible only for trashed records
RestoreAction::make();
ForceDeleteAction::make();
```

## Replicate Action

```php
use Laravilt\Actions\ReplicateAction;

ReplicateAction::make()
    ->excludeAttributes(['slug', 'published_at'])
    ->beforeReplicaSaved(fn ($replica) => $replica->name .= ' (Copy)')
    ->afterReplicaSaved(fn ($replica) => /* post-save logic */);
```

## Generator Commands

```bash
# Generate an action class
php artisan make:action ExportUserAction

# Generate an exporter class for ExportAction
php artisan laravilt:exporter UserExporter
php artisan laravilt:exporter CustomerExporter --model=Customer

# Generate an importer class for ImportAction
php artisan laravilt:importer UserImporter
php artisan laravilt:importer CustomerImporter --model=Customer
```

## Documentation

- **[Complete Documentation](docs/index.md)** - Full feature guide, API reference, and examples
- **[MCP Server Guide](docs/mcp-server.md)** - AI agent integration

## Configuration

Publish the config file:

```bash
php artisan vendor:publish --tag="laravilt-actions-config"
```

## Assets

Publish the plugin assets:

```bash
php artisan vendor:publish --tag="laravilt-actions-assets"
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
