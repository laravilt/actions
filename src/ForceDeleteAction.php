<?php

namespace Laravilt\Actions;

class ForceDeleteAction extends Action
{
    protected function setUp(): void
    {
        $this->name ??= 'force-delete';
        $this->label(__('actions::actions.buttons.force_delete'));
        $this->icon('Trash2');
        $this->color('destructive');
        $this->tooltip(__('actions::actions.tooltips.force_delete'));
        $this->requiresConfirmation();
        $this->modalHeading(__('actions::actions.modal.force_delete_title'));
        $this->modalDescription(__('actions::actions.modal.force_delete_description'));
        $this->preserveState(false);
        $this->hidden(function ($record) {
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
     * Sets the action closure to force delete the record and redirect to list page.
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

                    // Auto-configure action to force delete record and redirect
                    $this->action(function () use ($recordId, $resource, $modelClass) {
                        // Include soft deleted records in the query
                        $record = $modelClass::withTrashed()->findOrFail($recordId);
                        $record->forceDelete();

                        \Laravilt\Notifications\Notification::success()
                            ->title(__('notifications::notifications.success'))
                            ->body(__('actions::actions.messages.force_deleted'))
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
