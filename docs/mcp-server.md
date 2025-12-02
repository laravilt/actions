# MCP Server Integration

The Laravilt Actions package can be integrated with MCP (Model Context Protocol) server for AI agent interaction.

## Available Generator Command

### make:action
Generate a new action class.

**Usage:**
```bash
php artisan make:action ExportUserAction
php artisan make:action Admin/ExportUserAction
php artisan make:action ExportUserAction --force
```

**Arguments:**
- `name` (string, required): Action class name (StudlyCase)

**Options:**
- `--force`: Overwrite existing file

**Generated Structure:**
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

## Integration Example

MCP server tools should provide:

1. **list-actions** - List all action classes in the application
2. **action-info** - Get details about a specific action class
3. **generate-action** - Generate a new action class with specified configuration

## Security

The MCP server runs with the same permissions as your Laravel application. Ensure:
- Proper file permissions on the app/Actions directory
- Secure configuration of the MCP server
- Limited access to the MCP configuration file
