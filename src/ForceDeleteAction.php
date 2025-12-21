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

        // Auto-hide based on resource permissions and trashed state
        $this->hidden(function ($record) {
            // Only show for trashed records - hidden when NOT trashed
            if ($record === null) {
                return true;
            }

            $isTrashed = false;
            if (is_object($record) && method_exists($record, 'trashed')) {
                $isTrashed = $record->trashed();
            } elseif (is_array($record)) {
                $isTrashed = ! empty($record['deleted_at']);
            }

            if (! $isTrashed) {
                return true;
            }

            // Check permissions
            return ! $this->canForceDeleteRecord($record);
        });
    }

    /**
     * Check if the current user can force delete the record.
     */
    protected function canForceDeleteRecord(mixed $record): bool
    {
        // Get the Page class this action belongs to
        $pageClass = $this->getComponentClass();

        if (! $pageClass || ! method_exists($pageClass, 'getResource')) {
            return true; // No resource context, allow by default
        }

        $resource = $pageClass::getResource();

        if (! $resource) {
            return true;
        }

        // Check if resource has permission methods (from HasResourceAuthorization trait)
        if (method_exists($resource, 'canForceDelete')) {
            return $resource::canForceDelete($record);
        }

        return true; // No permission check available, allow by default
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

                        // Double-check permission before force delete
                        if (method_exists($resource, 'canForceDelete') && ! $resource::canForceDelete($record)) {
                            abort(403);
                        }

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
