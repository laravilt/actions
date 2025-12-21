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

        if (! $pageClass || ! method_exists($pageClass, 'getResource')) {
            return true; // No resource context, allow by default
        }

        $resource = $pageClass::getResource();

        if (! $resource) {
            return true;
        }

        // Check if resource has permission methods (from HasResourceAuthorization trait)
        if (method_exists($resource, 'canView')) {
            return $resource::canView($record);
        }

        return true; // No permission check available, allow by default
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
