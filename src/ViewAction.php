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
