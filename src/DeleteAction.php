<?php

namespace Laravilt\Actions;

class DeleteAction extends Action
{
    public static function make(?string $name = null): static
    {
        $action = parent::make($name ?? 'delete');

        return $action
            ->label('Delete')
            ->icon('Trash2')
            ->color('destructive')
            ->requiresConfirmation()
            ->preserveState(false); // Full page reload to follow redirect
    }

    /**
     * Auto-configure the action based on record context.
     * Sets the action closure to delete the record and redirect to list page.
     */
    public function resolveRecordContext(mixed $recordId): static
    {
        // Only set action if not already configured
        if (! $this->getAction()) {
            // Get the Page class this action belongs to
            $pageClass = $this->getComponentClass();

            if ($pageClass && method_exists($pageClass, 'getResource')) {
                $resource = $pageClass::getResource();

                if ($resource) {
                    $modelClass = $resource::getModel();

                    // Auto-configure action to delete record and redirect
                    $this->action(function () use ($recordId, $resource, $modelClass) {
                        $record = $modelClass::findOrFail($recordId);
                        $record->delete();

                        \Laravilt\Notifications\Notification::success()
                            ->title('Deleted successfully')
                            ->body('The record has been deleted.')
                            ->send();

                        return redirect($resource::getUrl('list'));
                    });

                    // Clear component context to make this a standalone action
                    // This ensures the action generates a standalone token instead of a component-based token
                    $this->clearComponent();
                }
            }
        }

        return $this;
    }
}
