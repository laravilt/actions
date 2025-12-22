<?php

namespace Laravilt\Actions;

use Laravilt\Notifications\Notification;

class DeleteBulkAction extends BulkAction
{
    protected ?string $model = null;

    protected ?string $resource = null;

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

        // Auto-hide if user doesn't have delete permission
        $this->visible(function () {
            return $this->checkDeletePermission();
        });
    }

    /**
     * Check if the current user has delete permission.
     */
    protected function checkDeletePermission(): bool
    {
        // If resource is set, use its permission check
        if ($this->resource && class_exists($this->resource)) {
            return $this->resource::canDelete();
        }

        // If model is set, try to find its resource and check permission
        if ($this->model) {
            $resource = $this->findResourceForModel($this->model);
            if ($resource) {
                return $resource::canDelete();
            }
        }

        // Default to checking delete permission directly
        $user = auth()->user();
        if (! $user || ! method_exists($user, 'hasPermissionTo')) {
            return true; // No auth system, allow by default
        }

        // Try to determine permission name from model
        if ($this->model) {
            $modelName = class_basename($this->model);
            $permissionName = 'delete_'.str($modelName)->snake()->toString();

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
