<?php

namespace Laravilt\Actions;

class DeleteAction extends Action
{
    protected function setUp(): void
    {
        $this->name ??= 'delete';
        $this->label(__('actions::actions.buttons.delete'));
        $this->icon('Trash2');
        $this->color('destructive');
        $this->tooltip(__('actions::actions.tooltips.delete'));
        $this->requiresConfirmation();
        $this->preserveState(false); // Full page reload to follow redirect

        // Auto-hide based on resource permissions and trashed state
        $this->hidden(function ($record) {
            // Hide for trashed records - can't soft-delete an already deleted record
            if ($record !== null) {
                if (is_object($record) && method_exists($record, 'trashed') && $record->trashed()) {
                    return true;
                }
                if (is_array($record) && ! empty($record['deleted_at'])) {
                    return true;
                }
            }

            // Check permissions
            return ! $this->canDeleteRecord($record);
        });
    }

    /**
     * Check if the current user can delete the record.
     */
    protected function canDeleteRecord(mixed $record): bool
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
        if (method_exists($resource, 'canDelete')) {
            return $resource::canDelete($record);
        }

        return true; // No permission check available, allow by default
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

                        // Double-check permission before delete
                        if (method_exists($resource, 'canDelete') && ! $resource::canDelete($record)) {
                            abort(403);
                        }

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
