<?php

namespace Laravilt\Actions;

use Laravilt\Notifications\Notification;

class RestoreBulkAction extends BulkAction
{
    protected ?string $model = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->name ??= 'restore';
        $this->label(__('actions::actions.buttons.restore'));
        $this->icon('RotateCcw');
        $this->color('success');
        $this->requiresConfirmation();
        $this->preserveState(false);
        $this->modalHeading(__('actions::actions.modal.bulk_restore_title'));
        $this->modalDescription(__('actions::actions.modal.bulk_restore_description'));
        $this->deselectRecordsAfterCompletion();
    }

    /**
     * Set the model class for this bulk action.
     */
    public function model(string $model): static
    {
        $this->model = $model;

        // Capture the model class in a local variable to avoid capturing $this
        $modelClass = $model;

        // Set up the default restore action
        $this->action(function (array $ids) use ($modelClass) {
            if (empty($ids)) {
                Notification::warning()
                    ->title(__('tables::tables.bulk.no_selection_title'))
                    ->body(__('tables::tables.bulk.no_selection_body'))
                    ->send();

                return;
            }

            // Restore soft deleted records
            $restored = $modelClass::withTrashed()->whereIn('id', $ids)->restore();

            Notification::success()
                ->title(__('actions::actions.states.success'))
                ->body(__('actions::actions.messages.bulk_restored', ['count' => $restored]))
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
