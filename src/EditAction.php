<?php

namespace Laravilt\Actions;

class EditAction extends Action
{
    public static function make(?string $name = null): static
    {
        $action = parent::make($name ?? 'edit');

        return $action
            ->label(__('actions::actions.buttons.edit'))
            ->icon('Pencil')
            ->color('warning')
            ->tooltip(__('actions::actions.tooltips.edit'))
            ->method('GET'); // Navigation action - use GET
    }

    /**
     * Convert to array, including visibility logic.
     * Edit action should be hidden for soft-deleted records.
     */
    public function toArray(): array
    {
        $data = parent::toArray();
        $data['hiddenWhenTrashed'] = true;

        return $data;
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
