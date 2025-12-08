<?php

namespace Laravilt\Actions;

class CreateAction extends Action
{
    public static function make(?string $name = null): static
    {
        $action = parent::make($name ?? 'create');

        return $action
            ->label('Create')
            ->icon('Plus')
            ->color('primary')
            ->method('GET'); // Navigation action - use GET
    }

    /**
     * Override toArray to auto-configure URL before serialization.
     */
    public function toArray(): array
    {
        // Auto-configure URL if not already set and we have component context
        if (! $this->getUrl()) {
            $pageClass = $this->getComponentClass();

            if ($pageClass && method_exists($pageClass, 'getResource')) {
                $resource = $pageClass::getResource();

                if ($resource) {
                    $this->url($resource::getUrl('create'));
                }
            }
        }

        return parent::toArray();
    }

    /**
     * CreateAction doesn't use record context, so this is a no-op.
     */
    public function resolveRecordContext(mixed $record): static
    {
        // No record-specific configuration needed for create action
        return $this;
    }
}
