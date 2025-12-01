<?php

namespace Laravilt\Actions\Concerns;

trait HasModal
{
    protected bool $requiresConfirmation = false;

    protected ?string $modalHeading = null;

    protected ?string $modalDescription = null;

    protected ?string $modalSubmitActionLabel = null;

    protected ?string $modalCancelActionLabel = null;

    protected ?string $modalIcon = null;

    protected ?string $modalIconColor = null;

    protected array $modalFormSchema = [];

    public function requiresConfirmation(bool $condition = true): static
    {
        $this->requiresConfirmation = $condition;

        return $this;
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
            return $label ? "Confirm {$label}" : 'Confirm Action';
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
            $label = method_exists($this, 'getLabel') ? $this->getLabel() : 'this action';
            return "Are you sure you want to {$label}?";
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
}
