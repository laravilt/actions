<?php

namespace Laravilt\Actions;

class ViewAction extends Action
{
    protected function setUp(): void
    {
        $this->name ??= 'view';
        $this->label(__('actions::actions.buttons.view'));
        $this->icon('Eye');
        $this->color('secondary');
        $this->tooltip(__('actions::actions.tooltips.view'));
        $this->method('GET'); // Navigation action - use GET

        // Auto-hide based on resource permissions
        $this->hidden(function ($record) {
            return ! $this->canViewRecord($record);
        });
    }

    /**
     * Check if the current user can view the record.
     */
    protected function canViewRecord(mixed $record): bool
    {
        // Get the Page class this action belongs to
        $pageClass = $this->getComponentClass();

        if ($pageClass && method_exists($pageClass, 'getResource')) {
            $resource = $pageClass::getResource();
            if ($resource && method_exists($resource, 'canView')) {
                return $resource::canView($record);
            }
        }

        // Fallback: check permission directly from record
        return $this->checkPermissionDirectly('view', $record);
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
     * Auto-configure the action based on record context.
     * Sets the URL to navigate to the view page for the given record.
     */
    public function resolveRecordContext(mixed $recordId): static
    {
        // Get the Page class this action belongs to
        $pageClass = $this->getComponentClass();

        if ($pageClass && method_exists($pageClass, 'getResource')) {
            $resource = $pageClass::getResource();

            if ($resource) {
                // Auto-configure URL to view page
                $this->url($resource::getUrl('view', ['record' => $recordId]));
            }
        }

        return $this;
    }
}
