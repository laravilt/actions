<?php

namespace Laravilt\Actions\Concerns;

trait HasModal
{
    protected bool $requiresConfirmation = false;

    protected bool $hasModal = false;

    protected ?string $modalHeading = null;

    protected ?string $modalDescription = null;

    protected ?string $modalSubmitActionLabel = null;

    protected ?string $modalCancelActionLabel = null;

    protected ?string $modalIcon = null;

    protected ?string $modalIconColor = null;

    protected array $modalFormSchema = [];

    protected array $modalInfolistSchema = [];

    protected ?\Closure $fillFormUsing = null;

    protected array $defaultFormData = [];

    public function requiresConfirmation(bool $condition = true): static
    {
        $this->requiresConfirmation = $condition;

        return $this;
    }

    /**
     * Enable modal display without confirmation UI (for forms/infolists).
     */
    public function modal(bool $condition = true): static
    {
        $this->hasModal = $condition;

        return $this;
    }

    public function hasModal(): bool
    {
        return $this->hasModal || $this->requiresConfirmation || ! empty($this->modalFormSchema) || ! empty($this->modalInfolistSchema);
    }

    public function modalHeading(?string $heading): static
    {
        $this->modalHeading = $heading;

        return $this;
    }

    public function modalDescription(?string $description): static
    {
        $this->modalDescription = $description;

        return $this;
    }

    public function modalSubmitActionLabel(?string $label): static
    {
        $this->modalSubmitActionLabel = $label;

        return $this;
    }

    public function modalCancelActionLabel(?string $label): static
    {
        $this->modalCancelActionLabel = $label;

        return $this;
    }

    public function modalIcon(?string $icon): static
    {
        $this->modalIcon = $icon;

        return $this;
    }

    public function modalIconColor(?string $color): static
    {
        $this->modalIconColor = $color;

        return $this;
    }

    public function modalFormSchema(array $schema): static
    {
        $this->modalFormSchema = $schema;

        return $this;
    }

    /**
     * Set the modal infolist schema for view-only modals.
     */
    public function modalInfolistSchema(array $schema): static
    {
        $this->modalInfolistSchema = $schema;

        return $this;
    }

    /**
     * Set the modal form schema (alias for modalFormSchema).
     */
    public function schema(array $schema): static
    {
        return $this->modalFormSchema($schema);
    }

    public function getRequiresConfirmation(): bool
    {
        return $this->requiresConfirmation;
    }

    public function getModalHeading(): ?string
    {
        // If heading is explicitly set, use it
        if ($this->modalHeading !== null) {
            return $this->modalHeading;
        }

        // If requires confirmation but no heading, generate default based on action label
        if ($this->requiresConfirmation) {
            $label = method_exists($this, 'getLabel') ? $this->getLabel() : null;

            return $label
                ? __('actions::actions.modal.confirm_action', ['action' => $label])
                : __('actions::actions.modal.confirm_title');
        }

        return null;
    }

    public function getModalDescription(): ?string
    {
        // If description is explicitly set, use it
        if ($this->modalDescription !== null) {
            return $this->modalDescription;
        }

        // If requires confirmation but no description, generate default based on action label
        if ($this->requiresConfirmation) {
            $label = method_exists($this, 'getLabel') ? $this->getLabel() : __('actions::actions.modal.this_action');

            return __('actions::actions.modal.confirm_action_description', ['action' => $label]);
        }

        return null;
    }

    public function getModalSubmitActionLabel(): ?string
    {
        return $this->modalSubmitActionLabel;
    }

    public function getModalCancelActionLabel(): ?string
    {
        return $this->modalCancelActionLabel;
    }

    public function getModalIcon(): ?string
    {
        // If icon is explicitly set, use it
        if ($this->modalIcon !== null) {
            return $this->modalIcon;
        }

        // If requires confirmation but no icon, use action's icon or default
        if ($this->requiresConfirmation) {
            $actionIcon = method_exists($this, 'getIcon') ? $this->getIcon() : null;

            return $actionIcon ?? 'alert-circle';
        }

        return null;
    }

    public function getModalIconColor(): ?string
    {
        // If color is explicitly set, use it
        if ($this->modalIconColor !== null) {
            return $this->modalIconColor;
        }

        // If requires confirmation but no color, use action's color or default
        if ($this->requiresConfirmation) {
            $actionColor = method_exists($this, 'getColor') ? $this->getColor() : null;

            return $actionColor ?? 'primary';
        }

        return null;
    }

    protected bool $requiresPassword = false;

    protected ?string $modalContent = null;

    protected bool $slideOver = false;

    protected ?string $modalWidth = null;

    protected bool $isViewOnly = false;

    public function slideOver(bool $condition = true): static
    {
        $this->slideOver = $condition;
        $this->requiresConfirmation($condition);

        return $this;
    }

    public function isSlideOver(): bool
    {
        return $this->slideOver;
    }

    public function requiresPassword(bool $condition = true): static
    {
        $this->requiresPassword = $condition;
        $this->requiresConfirmation($condition);

        return $this;
    }

    public function content(string $content): static
    {
        $this->modalContent = $content;

        return $this;
    }

    public function getRequiresPassword(): bool
    {
        return $this->requiresPassword;
    }

    public function getModalContent(): ?string
    {
        return $this->modalContent;
    }

    public function getModalFormSchema(): array
    {
        return $this->modalFormSchema;
    }

    public function getModalInfolistSchema(): array
    {
        return $this->modalInfolistSchema;
    }

    /**
     * Set a closure to fill the modal form with data based on the record.
     *
     * @param  \Closure  $callback  A closure that receives the record and returns an array of form data
     */
    public function fillForm(\Closure $callback): static
    {
        $this->fillFormUsing = $callback;

        return $this;
    }

    /**
     * Get the closure used to fill the form.
     */
    public function getFillFormUsing(): ?\Closure
    {
        return $this->fillFormUsing;
    }

    /**
     * Fill the form data for a specific record.
     *
     * @param  mixed  $record  The record to fill form data from
     * @return array The form data
     */
    public function getFilledFormData(mixed $record = null): array
    {
        if ($this->fillFormUsing === null) {
            return $this->defaultFormData;
        }

        // Only evaluate fillForm closure if we have a record
        // This prevents errors when serializing actions without record context
        if ($record === null) {
            return $this->defaultFormData;
        }

        try {
            return call_user_func($this->fillFormUsing, $record) ?? [];
        } catch (\Throwable $e) {
            // If fillForm fails, return default data
            return $this->defaultFormData;
        }
    }

    /**
     * Check if this action has a fillForm closure defined.
     */
    public function hasFillForm(): bool
    {
        return $this->fillFormUsing !== null;
    }

    /**
     * Set default form data (without record context).
     */
    public function defaultFormData(array $data): static
    {
        $this->defaultFormData = $data;

        return $this;
    }

    public function modalWidth(?string $width): static
    {
        $this->modalWidth = $width;

        return $this;
    }

    public function getModalWidth(): ?string
    {
        return $this->modalWidth;
    }

    public function isViewOnly(bool $condition = true): static
    {
        $this->isViewOnly = $condition;

        return $this;
    }

    public function getIsViewOnly(): bool
    {
        return $this->isViewOnly;
    }
}
