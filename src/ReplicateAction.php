<?php

declare(strict_types=1);

namespace Laravilt\Actions;

use Closure;
use Illuminate\Database\Eloquent\Model;

class ReplicateAction extends Action
{
    protected array $excludedAttributes = [];

    protected ?Closure $beforeReplicaSaved = null;

    protected ?Closure $afterReplicaSaved = null;

    protected bool $openInNewTab = false;

    protected function setUp(): void
    {
        $this->name ??= 'replicate';
        $this->label(__('actions::actions.replicate.label'));
        $this->icon('Copy');
        $this->color('gray');
        $this->tooltip(__('actions::actions.replicate.tooltip'));
        $this->requiresConfirmation();
        $this->modalHeading(__('actions::actions.replicate.modal.heading'));
        $this->modalDescription(__('actions::actions.replicate.modal.description'));
        $this->modalSubmitActionLabel(__('actions::actions.replicate.modal.submit'));
    }

    /**
     * Attributes to exclude when replicating.
     *
     * @param  array<string>  $attributes
     */
    public function excludeAttributes(array $attributes): static
    {
        $this->excludedAttributes = $attributes;

        return $this;
    }

    /**
     * Callback to run before the replica is saved.
     */
    public function beforeReplicaSaved(?Closure $callback): static
    {
        $this->beforeReplicaSaved = $callback;

        return $this;
    }

    /**
     * Callback to run after the replica is saved.
     */
    public function afterReplicaSaved(?Closure $callback): static
    {
        $this->afterReplicaSaved = $callback;

        return $this;
    }

    /**
     * Set whether to redirect to the edit page of the new record.
     */
    public function successRedirectUrl(?Closure $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function execute(mixed $record = null, array $data = []): mixed
    {
        if (! $record instanceof Model) {
            return null;
        }

        // Replicate the model
        $replica = $record->replicate($this->excludedAttributes);

        // Run before callback
        if ($this->beforeReplicaSaved) {
            call_user_func($this->beforeReplicaSaved, $replica, $record);
        }

        // Save the replica
        $replica->save();

        // Run after callback
        if ($this->afterReplicaSaved) {
            call_user_func($this->afterReplicaSaved, $replica, $record);
        }

        return $replica;
    }

    /**
     * Get the excluded attributes.
     */
    public function getExcludedAttributes(): array
    {
        return $this->excludedAttributes;
    }

    /**
     * Auto-configure the action based on record context.
     * Sets the action closure to replicate the record and redirect to edit page.
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
                    $excludedAttributes = $this->excludedAttributes;
                    $beforeReplicaSaved = $this->beforeReplicaSaved;
                    $afterReplicaSaved = $this->afterReplicaSaved;

                    // Auto-configure action to replicate record and redirect to edit page
                    $this->action(function () use ($recordId, $resource, $modelClass, $excludedAttributes, $beforeReplicaSaved, $afterReplicaSaved) {
                        $record = $modelClass::findOrFail($recordId);

                        // Replicate the model
                        $replica = $record->replicate($excludedAttributes);

                        // Run before callback
                        if ($beforeReplicaSaved) {
                            call_user_func($beforeReplicaSaved, $replica, $record);
                        }

                        // Save the replica
                        $replica->save();

                        // Run after callback
                        if ($afterReplicaSaved) {
                            call_user_func($afterReplicaSaved, $replica, $record);
                        }

                        \Laravilt\Notifications\Notification::success()
                            ->title(__('notifications::notifications.success'))
                            ->body(__('actions::actions.replicate.messages.success'))
                            ->send();

                        // Redirect to edit page of the new record
                        return redirect($resource::getUrl('edit', ['record' => $replica->getKey()]));
                    });

                    // Clear component context to make this a standalone action
                    $this->clearComponent();
                }
            }
        }

        return $this;
    }
}
