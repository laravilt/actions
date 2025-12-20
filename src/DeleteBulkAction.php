<?php

namespace Laravilt\Actions;

use Laravilt\Notifications\Notification;

class DeleteBulkAction extends BulkAction
{
    protected ?string $model = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->name ??= 'delete';
        $this->label(__('tables::tables.actions.bulk_delete'));
        $this->icon('Trash2');
        $this->color('destructive');
        $this->requiresConfirmation();
        $this->preserveState(false);
        $this->modalHeading(__('actions::actions.modal.delete_title'));
        $this->modalDescription(__('actions::actions.confirm_bulk_delete_description'));
        $this->deselectRecordsAfterCompletion();
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
                Notification::warning()
                    ->title(__('tables::tables.bulk.no_selection_title'))
                    ->body(__('tables::tables.bulk.no_selection_body'))
                    ->send();

                return;
            }

            $deleted = $modelClass::whereIn('id', $ids)->delete();

            Notification::success()
                ->title(__('actions::actions.states.success'))
                ->body(__('tables::tables.messages.bulk_deleted', ['count' => $deleted]))
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
