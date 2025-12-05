<?php

namespace Laravilt\Actions\Concerns;

trait HasIcon
{
    protected ?string $icon = null;

    protected ?string $iconPosition = 'before';

    public function icon(?string $icon, ?string $position = null): static
    {
        $this->icon = $icon;
        if ($position !== null) {
            $this->iconPosition = $position;
        }

        return $this;
    }

    public function iconPosition(string $position): static
    {
        $this->iconPosition = $position;

        return $this;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function getIconPosition(): string
    {
        return $this->iconPosition ?? 'before';
    }
}
