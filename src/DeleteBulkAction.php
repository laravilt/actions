<?php

namespace Laravilt\Actions;

class DeleteBulkAction extends BulkAction
{
    public static function make(?string $name = null): static
    {
        $action = parent::make($name ?? 'delete');

        return $action
            ->label('Delete Selected')
            ->icon('Trash2')
            ->color('destructive')
            ->requiresConfirmation();
    }
}
