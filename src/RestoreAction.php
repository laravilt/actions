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
            ->preserveState(false);
    }

    /**
     * Check if the action should be visible for the given record.
     * Only visible for soft-deleted records.
     */
    public function isVisibleForRecord(mixed $record): bool
    {
        // Check if the record is soft deleted (has deleted_at)
        if (is_object($record) && method_exists($record, 'trashed')) {
            return $record->trashed();
        }

        // For array data, check deleted_at
        if (is_array($record)) {
            return !empty($record['deleted_at']);
        }

        return false;
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

    /**
     * Convert to array, including visibility logic.
     */
    public function toArray(): array
    {
        $data = parent::toArray();
        $data['visibleWhenTrashed'] = true;
        $data['hiddenWhenNotTrashed'] = true;

        return $data;
    }
}
