<?php

namespace Laravilt\Actions;

class RestoreAction extends Action
{
    public static function make(?string $name = null): static
    {
        $action = parent::make($name ?? 'restore');

        return $action
            ->label(__('actions::actions.buttons.restore'))
            ->icon('RotateCcw')
            ->color('success')
            ->tooltip(__('actions::actions.tooltips.restore'))
            ->requiresConfirmation()
            ->modalHeading(__('actions::actions.modal.restore_title'))
            ->modalDescription(__('actions::actions.modal.restore_description'))
            ->preserveState(false)
            ->hidden(function ($record) {
                // Only show for trashed records - hidden when NOT trashed
                if ($record === null) {
                    return true;
                }
                if (is_object($record) && method_exists($record, 'trashed')) {
                    return ! $record->trashed();
                }
                if (is_array($record)) {
                    return empty($record['deleted_at']);
                }
                return true;
            });
    }

    /**
     * Auto-configure the action based on record context.
     * Sets the action closure to restore the record and redirect to list page.
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

                    // Auto-configure action to restore record and redirect
                    $this->action(function () use ($recordId, $resource, $modelClass) {
                        // Include soft deleted records in the query
                        $record = $modelClass::withTrashed()->findOrFail($recordId);
                        $record->restore();

                        \Laravilt\Notifications\Notification::success()
                            ->title(__('notifications::notifications.success'))
                            ->body(__('actions::actions.messages.restored'))
                            ->send();

                        // Get the list page name (could be 'list' or 'index' for simple resources)
                        $pages = $resource::getPages();
                        $listPageName = array_key_exists('list', $pages) ? 'list' : 'index';

                        return redirect($resource::getUrl($listPageName));
                    });

                    // Clear component context to make this a standalone action
                    $this->clearComponent();
                }
            }
        }

        return $this;
    }
}
