# Laravilt Actions Documentation

Complete action system with modal support, authorization, and Inertia.js integration for Laravilt.

## Table of Contents

1. [Getting Started](#getting-started)
2. [Architecture](#architecture)
3. [Action Generation](#action-generation)
4. [API Reference](#api-reference)
5. [MCP Server Integration](mcp-server.md)

## Overview

Laravilt Actions provides a comprehensive action system for building interactive UI components with:

- **Fluent Action Builder**: Create actions with icons, colors, and variants
- **Modal Support**: Built-in confirmation modals, slide-overs, and custom forms
- **Authorization**: Closure-based authorization checks
- **Multiple Variants**: Button, link, and icon button styles
- **URL Integration**: Support for external URLs and internal actions
- **Token-based Execution**: Secure action execution with encrypted tokens
- **Inertia.js Integration**: Seamless frontend integration with Vue 3

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

Action::make('edit')
    ->label('Edit')
    ->icon('pencil')
    ->modalHeading('Edit User')
    ->modalFormSchema([
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

## Generator Command

Generate action classes with the artisan command:

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

## Examples

### Bulk Actions

```php
Action::make('bulkDelete')
    ->label('Delete Selected')
    ->icon('trash-2')
    ->color('danger')
    ->requiresConfirmation()
    ->modalHeading('Delete Multiple Users')
    ->modalDescription('Are you sure you want to delete the selected users?')
    ->action(function ($records) {
        foreach ($records as $record) {
            $record->delete();
        }
    });
```

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
