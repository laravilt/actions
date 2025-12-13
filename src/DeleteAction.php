<?php

namespace Laravilt\Actions;

class DeleteAction extends Action
{
    public static function make(?string $name = null): static
    {
        $action = parent::make($name ?? 'delete');

        return $action
            ->label(__('actions::actions.buttons.delete'))
            ->icon('Trash2')
            ->color('destructive')
            ->tooltip(__('actions::actions.tooltips.delete'))
            ->requiresConfirmation()
            ->preserveState(false) // Full page reload to follow redirect
            ->hidden(function ($record) {
                // Hide for trashed records - can't soft-delete an already deleted record
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
                            ->title(__('notifications::notifications.success'))
                            ->body(__('notifications::notifications.record_deleted'))
                            ->send();

                        // Get the list page name (could be 'list' or 'index' for simple resources)
                        $pages = $resource::getPages();
                        $listPageName = array_key_exists('list', $pages) ? 'list' : 'index';

                        return redirect($resource::getUrl($listPageName));
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
