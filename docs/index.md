# Laravilt Actions Documentation

Complete action system with modal support, authorization, and Inertia.js integration for Laravilt.

## Table of Contents

1. [Getting Started](#getting-started)
2. [Architecture](#architecture)
3. [Action Generation](#action-generation)
4. [Built-in Actions](#built-in-actions)
5. [Export & Import](#export--import)
6. [Soft Delete Actions](#soft-delete-actions)
7. [Replicate Action](#replicate-action)
8. [Bulk Actions](#bulk-actions)
9. [API Reference](#api-reference)
10. [MCP Server Integration](mcp-server.md)

## Overview

Laravilt Actions provides a comprehensive action system for building interactive UI components with:

- **Fluent Action Builder**: Create actions with icons, colors, and variants
- **Modal Support**: Built-in confirmation modals, slide-overs, and custom forms
- **Authorization**: Closure-based authorization checks
- **Multiple Variants**: Button, link, and icon button styles
- **URL Integration**: Support for external URLs and internal actions
- **Token-based Execution**: Secure action execution with encrypted tokens
- **Inertia.js Integration**: Seamless frontend integration with Vue 3
- **Export/Import**: Excel and CSV export/import powered by Laravel Excel
- **Soft Delete Support**: Built-in restore and force delete actions
- **Record Replication**: Duplicate records with customizable attributes

## Quick Start

```bash
# Generate a new action class
php artisan make:action ExportUserAction

# Use in your code
use App\Actions\ExportUserAction;

$action = ExportUserAction::make()
    ->label('Export Users')
    ->icon('download')
    ->color('primary')
    ->requiresConfirmation()
    ->action(function ($record, $data) {
        // Export logic here
    });
```

## Key Features

### 🎨 Action Variants
- **Button**: Standard button style
- **Link**: Text link with underline
- **Icon Button**: Icon-only button

### 🔒 Authorization
- Closure-based authorization
- Per-action authorization checks
- Record-level authorization

### 📊 Modal Support
- Confirmation modals
- Custom form schemas
- Password confirmation
- Slide-over panels
- Custom icons and colors

### 🎯 Flexible Configuration
- Colors (primary, secondary, success, danger, warning, info)
- Icons (Lucide icon library)
- Sizes (xs, sm, md, lg, xl)
- Tooltips
- Disabled states
- Hidden states

### 🔗 URL Handling
- External URLs
- Internal actions
- New tab support
- Custom action URLs

## System Requirements

- PHP 8.3+
- Laravel 12+
- Inertia.js v2+
- Vue 3

## Installation

```bash
composer require laravilt/actions
```

The service provider is auto-discovered and will register automatically.

## Configuration

Publish the configuration:

```bash
php artisan vendor:publish --tag=laravilt-actions-config
```

## Basic Usage

### Creating a Simple Action

```php
use Laravilt\Actions\Action;

$action = Action::make('delete')
    ->label('Delete')
    ->icon('trash-2')
    ->color('danger')
    ->requiresConfirmation()
    ->modalHeading('Delete User')
    ->modalDescription('Are you sure you want to delete this user?')
    ->action(function ($record) {
        $record->delete();
    });
```

### Using with Tables

```php
use Laravilt\Tables\Table;
use Laravilt\Tables\Columns\TextColumn;
use Laravilt\Actions\Action;

Table::make()
    ->columns([
        TextColumn::make('name'),
        TextColumn::make('email'),
    ])
    ->actions([
        Action::make('edit')
            ->label('Edit')
            ->icon('pencil')
            ->url(fn ($record) => route('users.edit', $record)),

        Action::make('delete')
            ->label('Delete')
            ->icon('trash-2')
            ->color('danger')
            ->requiresConfirmation()
            ->action(fn ($record) => $record->delete()),
    ]);
```

### Authorization

```php
use Laravilt\Actions\Action;

Action::make('edit')
    ->label('Edit')
    ->icon('pencil')
    ->authorize(fn ($record) => auth()->user()->can('update', $record))
    ->action(function ($record, $data) {
        $record->update($data);
    });
```

### Modal with Form Schema

```php
use Laravilt\Forms\Components\TextInput;
use Laravilt\Forms\Components\Textarea;
use Laravilt\Actions\Action;

Action::make('edit')
    ->label('Edit')
    ->icon('pencil')
    ->modalHeading('Edit User')
    ->schema([
        TextInput::make('name')
            ->label('Name')
            ->required(),
        TextInput::make('email')
            ->label('Email')
            ->email()
            ->required(),
        Textarea::make('bio')
            ->label('Bio'),
    ])
    ->action(function ($record, $data) {
        $record->update($data);
    });
```

## Action Variants

### Button (Default)

```php
Action::make('save')
    ->label('Save')
    ->button(); // Explicit
```

### Link

```php
Action::make('view')
    ->label('View Details')
    ->link()
    ->url('/users/123');
```

### Icon Button

```php
Action::make('delete')
    ->icon('trash-2')
    ->iconButton()
    ->tooltip('Delete User');
```

## Available Colors

- `primary` - Primary brand color
- `secondary` - Secondary color
- `success` - Green for success actions
- `danger` - Red for destructive actions
- `warning` - Orange for warning actions
- `info` - Blue for informational actions

## Available Sizes

- `xs` - Extra small
- `sm` - Small
- `md` - Medium (default)
- `lg` - Large
- `xl` - Extra large

## Generator Commands

Generate action, exporter, and importer classes with artisan commands:

### Generate Action Class

```bash
# Basic action
php artisan make:action ExportUserAction

# With custom namespace
php artisan make:action Admin/ExportUserAction

# Force overwrite existing file
php artisan make:action ExportUserAction --force
```

Generated action structure:

```php
<?php

namespace App\Actions;

use Laravilt\Actions\Action;

class ExportUserAction
{
    public static function make(): Action
    {
        return Action::make('export')
            ->label('Export')
            ->icon('download')
            ->color('primary')
            ->action(function ($record, $data) {
                // Your action logic here
            });
    }
}
```

### Generate Exporter Class

```bash
# Basic exporter (auto-detects model from name)
php artisan laravilt:exporter UserExporter

# With explicit model
php artisan laravilt:exporter CustomerExporter --model=Customer

# Force overwrite
php artisan laravilt:exporter UserExporter --force
```

Generated exporter structure:

```php
<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class UserExporter implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    public function query()
    {
        return User::query();
    }

    public function headings(): array
    {
        return ['ID', 'Name', 'Created At', 'Updated At'];
    }

    public function map($row): array
    {
        return [
            $row->id,
            $row->name,
            $row->created_at?->format('Y-m-d H:i:s'),
            $row->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
```

### Generate Importer Class

```bash
# Basic importer (auto-detects model from name)
php artisan laravilt:importer UserImporter

# With explicit model
php artisan laravilt:importer CustomerImporter --model=Customer

# Force overwrite
php artisan laravilt:importer UserImporter --force
```

Generated importer structure:

```php
<?php

namespace App\Imports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class UserImporter implements ToModel, WithHeadingRow, WithValidation, SkipsEmptyRows
{
    public function model(array $row)
    {
        return new User([
            'name' => $row['name'],
            // Add more fields as needed
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
        ];
    }
}
```

## Integration with Frontend

Actions are automatically serialized to Inertia props:

```javascript
// In your Vue component
import { useAction } from '@/composables/useAction'

const { execute } = useAction()

const handleAction = async (action, record) => {
  await execute(action, record)
}
```

## API Reference

### Action Class

#### Static Methods

- `make(?string $name = null): static` - Create a new action instance

#### Configuration Methods

- `name(?string $name): static` - Set the action name
- `label(?string $label): static` - Set the action label
- `icon(?string $icon): static` - Set the icon (Lucide icons)
- `color(?string $color): static` - Set the color
- `size(?string $size): static` - Set the size
- `tooltip(?string $tooltip): static` - Set the tooltip text

#### Variant Methods

- `button(): static` - Set variant to button
- `link(): static` - Set variant to link
- `iconButton(): static` - Set variant to icon button

#### State Methods

- `disabled(bool $condition = true): static` - Disable the action
- `outlined(bool $condition = true): static` - Make the action outlined
- `hidden(bool|Closure $condition = true): static` - Hide the action

#### URL Methods

- `url(string|Closure $url): static` - Set the URL
- `openUrlInNewTab(bool $condition = true): static` - Open URL in new tab
- `actionUrl(string $url): static` - Set custom action execution URL

#### Authorization

- `authorize(?Closure $authorize): static` - Set authorization callback
- `canAuthorize(mixed $record = null): bool` - Check if action is authorized

#### Action Execution

- `action(?Closure $action): static` - Set the action callback
- `execute(mixed $record = null, array $data = []): mixed` - Execute the action

#### Modal Methods

- `requiresConfirmation(bool $condition = true): static` - Require confirmation
- `modalHeading(?string $heading): static` - Set modal heading
- `modalDescription(?string $description): static` - Set modal description
- `modalSubmitActionLabel(?string $label): static` - Set submit button label
- `modalCancelActionLabel(?string $label): static` - Set cancel button label
- `modalIcon(?string $icon): static` - Set modal icon
- `modalIconColor(?string $color): static` - Set modal icon color
- `modalFormSchema(array $schema): static` - Set modal form schema
- `modalContent(?string $content): static` - Set custom modal content
- `requiresPassword(bool $condition = true): static` - Require password confirmation
- `slideOver(bool $condition = true): static` - Use slide-over instead of modal

#### Serialization

- `toArray(): array` - Convert to array
- `toInertiaProps(): array` - Convert to Inertia props

## Best Practices

1. **Use Action Classes**: Create dedicated action classes for complex actions
2. **Authorization**: Always add authorization checks for destructive actions
3. **Confirmation**: Use confirmation modals for destructive or irreversible actions
4. **Icons**: Use meaningful Lucide icons that match the action purpose
5. **Colors**: Use semantic colors (danger for delete, success for approve, etc.)
6. **Tooltips**: Add tooltips for icon-only buttons
7. **Form Validation**: Add validation to modal form schemas

## Built-in Actions

Laravilt provides several pre-configured action types for common operations:

### ViewAction

Navigate to the view page for a record:

```php
use Laravilt\Actions\ViewAction;

ViewAction::make()
    ->authorize(fn ($record) => auth()->user()->can('view', $record));
```

### EditAction

Navigate to the edit page for a record:

```php
use Laravilt\Actions\EditAction;

EditAction::make()
    ->authorize(fn ($record) => auth()->user()->can('update', $record));
```

### CreateAction

Navigate to the create page:

```php
use Laravilt\Actions\CreateAction;

CreateAction::make()
    ->authorize(fn () => auth()->user()->can('create', User::class));
```

## Export & Import

Laravilt Actions includes powerful export and import functionality powered by Laravel Excel.

### ExportAction

Export data to Excel, CSV, or other formats:

```php
use Laravilt\Actions\ExportAction;

// Basic export with exporter class
ExportAction::make()
    ->exporter(UserExporter::class)
    ->fileName('users.xlsx');

// Export as CSV
ExportAction::make()
    ->exporter(UserExporter::class)
    ->csv()
    ->fileName('users.csv');

// Queue export for large datasets
ExportAction::make()
    ->exporter(UserExporter::class)
    ->queue()
    ->disk('exports');
```

#### ExportAction Methods

| Method | Description |
|--------|-------------|
| `exporter(string $class)` | Set the exporter class (must implement Laravel Excel export interface) |
| `fileName(string $name)` | Set the download filename |
| `xlsx()` | Export as XLSX format (default) |
| `csv()` | Export as CSV format |
| `writerType(string $type)` | Set custom writer type |
| `queue(bool $queue = true)` | Queue the export for background processing |
| `disk(string $disk)` | Set storage disk for queued exports |
| `modifyQueryUsing(Closure $callback)` | Modify the export query |
| `columns(array $columns)` | Set columns to export |
| `headings(array $headings)` | Set column headings |

#### Creating an Exporter Class

```php
<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class UserExporter implements FromQuery, WithHeadings, WithMapping
{
    public function query()
    {
        return User::query();
    }

    public function headings(): array
    {
        return ['ID', 'Name', 'Email', 'Created At'];
    }

    public function map($user): array
    {
        return [
            $user->id,
            $user->name,
            $user->email,
            $user->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
```

### ImportAction

Import data from Excel or CSV files:

```php
use Laravilt\Actions\ImportAction;

// Basic import with importer class
ImportAction::make()
    ->importer(UserImporter::class);

// Import from CSV
ImportAction::make()
    ->importer(UserImporter::class)
    ->csv();

// Queue import for large files
ImportAction::make()
    ->importer(UserImporter::class)
    ->queue();

// Custom accepted file types
ImportAction::make()
    ->importer(UserImporter::class)
    ->acceptedFileTypes([
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'text/csv',
    ]);
```

#### ImportAction Methods

| Method | Description |
|--------|-------------|
| `importer(string $class)` | Set the importer class (must implement Laravel Excel import interface) |
| `xlsx()` | Import from XLSX format (default) |
| `csv()` | Import from CSV format |
| `readerType(string $type)` | Set custom reader type |
| `queue(bool $queue = true)` | Queue the import for background processing |
| `disk(string $disk)` | Set storage disk for reading files |
| `chunkSize(int $size)` | Set chunk size for large imports |
| `beforeImport(Closure $callback)` | Callback before import starts |
| `afterImport(Closure $callback)` | Callback after import completes |
| `acceptedFileTypes(array $types)` | Set accepted MIME types for upload |

#### Creating an Importer Class

```php
<?php

namespace App\Imports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class UserImporter implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new User([
            'name' => $row['name'],
            'email' => $row['email'],
            'password' => bcrypt('password'),
        ]);
    }
}
```

## Soft Delete Actions

Laravilt provides specialized actions for handling soft-deleted records.

### DeleteAction

Soft delete a record. Automatically hidden for already-trashed records:

```php
use Laravilt\Actions\DeleteAction;

DeleteAction::make()
    ->authorize(fn ($record) => auth()->user()->can('delete', $record));
```

### RestoreAction

Restore a soft-deleted record. Automatically visible only for trashed records:

```php
use Laravilt\Actions\RestoreAction;

RestoreAction::make()
    ->authorize(fn ($record) => auth()->user()->can('restore', $record));
```

### ForceDeleteAction

Permanently delete a record. Automatically visible only for trashed records:

```php
use Laravilt\Actions\ForceDeleteAction;

ForceDeleteAction::make()
    ->authorize(fn ($record) => auth()->user()->can('forceDelete', $record));
```

### Soft Delete Visibility

The soft delete actions automatically handle visibility based on the record's trashed state:

| Action | Visible When | Hidden When |
|--------|--------------|-------------|
| `DeleteAction` | Record is not trashed | Record is trashed |
| `RestoreAction` | Record is trashed | Record is not trashed |
| `ForceDeleteAction` | Record is trashed | Record is not trashed |

## Replicate Action

Duplicate records with customizable attribute exclusions and callbacks.

### Basic Usage

```php
use Laravilt\Actions\ReplicateAction;

ReplicateAction::make();
```

### Exclude Attributes

Exclude specific attributes from being copied:

```php
ReplicateAction::make()
    ->excludeAttributes(['slug', 'published_at', 'views_count']);
```

### Before/After Callbacks

Customize the replica before or after saving:

```php
ReplicateAction::make()
    ->excludeAttributes(['slug'])
    ->beforeReplicaSaved(function ($replica, $original) {
        $replica->name = $original->name . ' (Copy)';
        $replica->slug = Str::slug($replica->name);
        $replica->status = 'draft';
    })
    ->afterReplicaSaved(function ($replica, $original) {
        // Copy relationships
        $replica->tags()->sync($original->tags->pluck('id'));

        // Log the replication
        activity()->log("Replicated record {$original->id} to {$replica->id}");
    });
```

#### ReplicateAction Methods

| Method | Description |
|--------|-------------|
| `excludeAttributes(array $attributes)` | Attributes to exclude from replication |
| `beforeReplicaSaved(Closure $callback)` | Callback before replica is saved (receives $replica, $original) |
| `afterReplicaSaved(Closure $callback)` | Callback after replica is saved (receives $replica, $original) |
| `successRedirectUrl(Closure $url)` | Custom redirect URL after replication |

## Bulk Actions

Actions that operate on multiple selected records.

### BulkAction

Base class for bulk operations:

```php
use Laravilt\Actions\BulkAction;

BulkAction::make('activate')
    ->label('Activate Selected')
    ->icon('check-circle')
    ->color('success')
    ->requiresConfirmation()
    ->action(function ($records) {
        $records->each->activate();
    })
    ->deselectRecordsAfterCompletion();
```

### DeleteBulkAction

Bulk soft delete records:

```php
use Laravilt\Actions\DeleteBulkAction;

DeleteBulkAction::make()
    ->authorize(fn () => auth()->user()->can('deleteAny', User::class));
```

### RestoreBulkAction

Bulk restore soft-deleted records:

```php
use Laravilt\Actions\RestoreBulkAction;

RestoreBulkAction::make()
    ->authorize(fn () => auth()->user()->can('restoreAny', User::class));
```

### ForceDeleteBulkAction

Bulk permanently delete records:

```php
use Laravilt\Actions\ForceDeleteBulkAction;

ForceDeleteBulkAction::make()
    ->authorize(fn () => auth()->user()->can('forceDeleteAny', User::class));
```

## Examples

### Conditional Actions

```php
Action::make('approve')
    ->label('Approve')
    ->icon('check-circle')
    ->color('success')
    ->hidden(fn ($record) => $record->isApproved())
    ->authorize(fn ($record) => auth()->user()->can('approve', $record))
    ->action(function ($record) {
        $record->update(['status' => 'approved']);
    });
```

### External Link Action

```php
Action::make('viewProfile')
    ->label('View Profile')
    ->icon('external-link')
    ->link()
    ->url(fn ($record) => "https://example.com/profile/{$record->id}")
    ->openUrlInNewTab();
```

## Support

- GitHub Issues: github.com/laravilt/actions
- Documentation: docs.laravilt.com
- Discord: discord.laravilt.com
