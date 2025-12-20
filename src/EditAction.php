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
        $this->hidden(function ($record) {
            // Hide for trashed records - can't edit a deleted record
            if ($record === null) {
                return false;
            }
            if (is_object($record) && method_exists($record, 'trashed')) {
                return $record->trashed();
            }
            if (is_array($record)) {
                return ! empty($record['deleted_at']);
            }

            return false;
        });
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
