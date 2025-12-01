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
        return $this->modalHeading;
    }

    public function getModalDescription(): ?string
    {
        return $this->modalDescription;
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
        return $this->modalIcon;
    }

    public function getModalIconColor(): ?string
    {
        return $this->modalIconColor;
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
