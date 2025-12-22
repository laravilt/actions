<?php

namespace Laravilt\Actions;

class EditAction extends Action
{
    protected function setUp(): void
    {
        $this->name ??= 'edit';
        $this->label(__('actions::actions.buttons.edit'));
        $this->icon('Pencil');
        $this->color('warning');
        $this->tooltip(__('actions::actions.tooltips.edit'));
        $this->method('GET'); // Navigation action - use GET

        // Auto-hide based on resource permissions and trashed state
        $this->hidden(function ($record) {
            // Hide for trashed records - can't edit a deleted record
            if ($record !== null) {
                if (is_object($record) && method_exists($record, 'trashed') && $record->trashed()) {
                    return true;
                }
                if (is_array($record) && ! empty($record['deleted_at'])) {
                    return true;
                }
            }

            // Check permissions
            return ! $this->canEditRecord($record);
        });
    }

    /**
     * Check if the current user can edit the record.
     */
    protected function canEditRecord(mixed $record): bool
    {
        // Get the Page class this action belongs to
        $pageClass = $this->getComponentClass();

        if ($pageClass && method_exists($pageClass, 'getResource')) {
            $resource = $pageClass::getResource();
            if ($resource && method_exists($resource, 'canUpdate')) {
                return $resource::canUpdate($record);
            }
        }

        // Fallback: check permission directly from record
        return $this->checkPermissionDirectly('update', $record);
    }

    /**
     * Check permission directly using the record's model class.
     */
    protected function checkPermissionDirectly(string $action, mixed $record): bool
    {
        $user = auth()->user();
        if (! $user || ! method_exists($user, 'hasPermissionTo')) {
            return true;
        }

        if ($record) {
            $modelName = is_object($record) ? class_basename($record) : null;
            if ($modelName) {
                $permissionName = $action.'_'.str($modelName)->snake()->toString();
                try {
                    return $user->hasPermissionTo($permissionName);
                } catch (\Exception $e) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Placeholder for old method - no longer used.
     */
    protected function oldCanEditRecord(mixed $record): bool
    {
        $pageClass = $this->getComponentClass();
        $resource = $pageClass ? $pageClass::getResource() : null;

        // Check if resource has permission methods (from HasResourceAuthorization trait)
        if (method_exists($resource, 'canUpdate')) {
            return $resource::canUpdate($record);
        }

        return true; // No permission check available, allow by default
    }

    /**
     * Auto-configure the action based on record context.
     * Sets the URL to navigate to the edit page for the given record.
     */
    public function resolveRecordContext(mixed $recordId): static
    {
        // Get the Page class this action belongs to
        $pageClass = $this->getComponentClass();

        if ($pageClass && method_exists($pageClass, 'getResource')) {
            $resource = $pageClass::getResource();

            if ($resource) {
                // Auto-configure URL to edit page
                $this->url($resource::getUrl('edit', ['record' => $recordId]));
            }
        }

        return $this;
    }
}
