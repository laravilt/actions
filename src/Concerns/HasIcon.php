<?php

namespace Laravilt\Actions\Concerns;

trait HasIcon
{
    protected ?string $icon = null;

    protected ?string $iconPosition = 'before';

    public function icon(?string $icon, ?string $position = 'before'): static
    {
        $this->icon = $icon;
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
