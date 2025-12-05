<?php

namespace Laravilt\Actions;

class EditAction extends Action
{
    public static function make(?string $name = null): static
    {
        $action = parent::make($name ?? 'edit');

        return $action
            ->label('Edit')
            ->icon('Pencil')
            ->color('warning');
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
