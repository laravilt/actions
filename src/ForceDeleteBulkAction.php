<?php

namespace Laravilt\Actions;

use Laravilt\Notifications\Notification;

class ForceDeleteBulkAction extends BulkAction
{
    protected ?string $model = null;

    public static function make(?string $name = null): static
    {
        $action = parent::make($name ?? 'force-delete');

        return $action
            ->label(__('actions::actions.buttons.force_delete'))
            ->icon('Trash2')
            ->color('destructive')
            ->requiresConfirmation()
            ->preserveState(false)
            ->modalHeading(__('actions::actions.modal.bulk_force_delete_title'))
            ->modalDescription(__('actions::actions.modal.bulk_force_delete_description'))
            ->deselectRecordsAfterCompletion();
    }

    /**
     * Set the model class for this bulk action.
     */
    public function model(string $model): static
    {
        $this->model = $model;

        // Capture the model class in a local variable to avoid capturing $this
        $modelClass = $model;

        // Set up the default force delete action
        $this->action(function (array $ids) use ($modelClass) {
            if (empty($ids)) {
                Notification::warning()
                    ->title(__('tables::tables.bulk.no_selection_title'))
                    ->body(__('tables::tables.bulk.no_selection_body'))
                    ->send();

                return;
            }

            // Force delete records (permanently remove from database)
            $deleted = $modelClass::withTrashed()->whereIn('id', $ids)->forceDelete();

            Notification::success()
                ->title(__('actions::actions.states.success'))
                ->body(__('actions::actions.messages.bulk_force_deleted', ['count' => $deleted]))
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

    /**
     * Convert to array, including visibility logic.
     */
    public function toArray(): array
    {
        $data = parent::toArray();
        $data['visibleWhenTrashed'] = true;
        $data['hiddenWhenNotTrashed'] = true;

        return $data;
    }
}
