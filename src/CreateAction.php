<?php

namespace Laravilt\Actions;

class CreateAction extends Action
{
    protected ?string $modelClass = null;

    protected array $formSchema = [];

    protected bool $isConfigured = false;

    protected function setUp(): void
    {
        $this->name ??= 'create';
        $this->icon('Plus');
        $this->color('primary');

        // Auto-hide based on resource permissions
        $this->hidden(function ($record) {
            return ! $this->canCreateRecord();
        });
    }

    /**
     * Check if the current user can create records.
     */
    protected function canCreateRecord(): bool
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
        if (method_exists($resource, 'canCreate')) {
            return $resource::canCreate();
        }

        return true; // No permission check available, allow by default
    }

    /**
     * Set the model class for creating records.
     */
    public function model(string $modelClass): static
    {
        $this->modelClass = $modelClass;

        return $this;
    }

    /**
     * Get the model class.
     */
    public function getModelClass(): ?string
    {
        return $this->modelClass;
    }

    /**
     * Configure the action to use a modal with form schema.
     * This transforms the action from navigation to modal-based.
     */
    public function formSchema(array $schema): static
    {
        $this->formSchema = $schema;
        $this->modal(true);
        $this->modalFormSchema($schema);
        $this->modalSubmitActionLabel(__('actions::actions.buttons.create'));
        $this->modalCancelActionLabel(__('actions::actions.buttons.cancel'));
        $this->modalWidth('lg');
        $this->preserveState(false);
        $this->method('POST');
        $this->isConfigured = true;

        return $this;
    }

    /**
     * Set the action closure for creating records.
     * If model is set, provides a default implementation.
     */
    public function using(?\Closure $callback = null): static
    {
        if ($callback) {
            return $this->action($callback);
        }

        // Default implementation using model class
        if ($this->modelClass) {
            $modelClass = $this->modelClass;

            return $this->action(function ($record, array $data) use ($modelClass) {
                $newRecord = new $modelClass;
                $newRecord->fill($data);
                $newRecord->save();

                \Laravilt\Notifications\Notification::success()
                    ->title(__('notifications::notifications.success'))
                    ->body(__('actions::actions.messages.created'))
                    ->send();

                return $newRecord;
            });
        }

        return $this;
    }

    /**
     * Auto-configure based on page context.
     */
    protected function autoConfigureFromPage(): static
    {
        if ($this->isConfigured) {
            return $this;
        }

        $pageClass = $this->getComponentClass();

        if (! $pageClass) {
            return $this;
        }

        // Check if page has a resource
        if (! method_exists($pageClass, 'getResource')) {
            return $this;
        }

        $resource = $pageClass::getResource();
        if (! $resource) {
            return $this;
        }

        // Get resource info
        $modelClass = $resource::getModel();
        $slug = $resource::getSlug();
        $label = $resource::getLabel();

        // Check if this is a ManageRecords page (modal-based CRUD)
        if (is_subclass_of($pageClass, \Laravilt\Panel\Pages\ManageRecords::class)) {
            // Get the page instance to access form schema
            $page = app($pageClass);
            if (method_exists($page, 'getPanel')) {
                $panel = \Laravilt\Panel\Facades\Panel::getCurrent();
                if ($panel) {
                    $page->panel($panel);
                }
            }

            // Get form schema from page
            $formSchema = [];
            if (method_exists($page, 'form')) {
                $schema = $page->form(\Laravilt\Schemas\Schema::make());
                $formSchema = $schema->getSchema();
            }

            // Configure as modal action
            $this->stableId("{$slug}_create");
            $this->label(__('actions::actions.buttons.create').' '.$label);
            $this->modalHeading(__('actions::actions.buttons.create').' '.$label);
            $this->model($modelClass);
            $this->formSchema($formSchema);
            $this->using();
        } else {
            // Full resource - configure as navigation action
            $this->label(__('actions::actions.buttons.create').' '.$label);
            $this->method('GET');
            $this->url($resource::getUrl('create'));
        }

        $this->isConfigured = true;

        return $this;
    }

    /**
     * Override toArray to auto-configure based on page context.
     */
    public function toArray(): array
    {
        $this->autoConfigureFromPage();

        return parent::toArray();
    }
}
