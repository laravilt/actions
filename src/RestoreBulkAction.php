<?php

namespace Laravilt\Actions;

use Laravilt\Notifications\Notification;

class RestoreBulkAction extends BulkAction
{
    protected ?string $model = null;

    protected ?string $resource = null;

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

        // Auto-hide if user doesn't have restore permission
        $this->visible(function () {
            return $this->checkRestorePermission();
        });
    }

    /**
     * Check if the current user has restore permission.
     */
    protected function checkRestorePermission(): bool
    {
        // If resource is set, use its permission check
        if ($this->resource && class_exists($this->resource)) {
            return $this->resource::canRestore();
        }

        // If model is set, try to find its resource and check permission
        if ($this->model) {
            $resource = $this->findResourceForModel($this->model);
            if ($resource) {
                return $resource::canRestore();
            }
        }

        // Default to checking restore permission directly
        $user = auth()->user();
        if (! $user || ! method_exists($user, 'hasPermissionTo')) {
            return true; // No auth system, allow by default
        }

        // Try to determine permission name from model
        if ($this->model) {
            $modelName = class_basename($this->model);
            $permissionName = 'restore_'.str($modelName)->snake()->toString();

            try {
                return $user->hasPermissionTo($permissionName);
            } catch (\Exception $e) {
                return false;
            }
        }

        return true;
    }

    /**
     * Find the resource class for a model.
     */
    protected function findResourceForModel(string $model): ?string
    {
        // Get all registered resources from the current panel
        try {
            $panel = app('laravilt.panel.current');
        } catch (\Exception $e) {
            return null;
        }

        if (! $panel) {
            return null;
        }

        foreach ($panel->getResources() as $resource) {
            if ($resource::getModel() === $model) {
                return $resource;
            }
        }

        return null;
    }

    /**
     * Set the resource class for permission checking.
     */
    public function resource(string $resource): static
    {
        $this->resource = $resource;

        return $this;
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
