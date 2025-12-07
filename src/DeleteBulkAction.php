<?php

namespace Laravilt\Actions;

use Laravilt\Notifications\Notification;

class DeleteBulkAction extends BulkAction
{
    protected ?string $model = null;

    public static function make(?string $name = null): static
    {
        $action = parent::make($name ?? 'delete');

        return $action
            ->label('Delete Selected')
            ->icon('Trash2')
            ->color('destructive')
            ->requiresConfirmation()
            ->modalHeading('Delete Selected Records')
            ->modalDescription('Are you sure you want to delete the selected records? This action cannot be undone.')
            ->deselectRecordsAfterCompletion();
    }

    /**
     * Set the model class for this bulk action.
     */
    public function model(string $model): static
    {
        $this->model = $model;

        // Capture the model class in a local variable to avoid capturing $this
        // This prevents serialization issues with SerializableClosure
        $modelClass = $model;

        // Set up the default delete action
        $this->action(function (array $ids) use ($modelClass) {
            if (empty($ids)) {
                Notification::make()
                    ->title('No records selected')
                    ->warning()
                    ->send();

                return;
            }

            $deleted = $modelClass::whereIn('id', $ids)->delete();

            Notification::make()
                ->title('Records deleted')
                ->body("{$deleted} record(s) were deleted successfully.")
                ->success()
                ->send();
        });

        return $this;
    }

    /**
     * Get the model class.
     */
    public function getModel(): ?string
    {
        return $this->model;
    }
}
