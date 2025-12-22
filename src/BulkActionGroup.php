<?php

namespace Laravilt\Actions;

use Illuminate\Contracts\Support\Arrayable;

class BulkActionGroup implements Arrayable
{
    protected array $actions = [];

    protected ?string $label = null;

    protected ?string $icon = null;

    protected ?string $color = null;

    public function __construct(array $actions = [])
    {
        $this->actions = $actions;
    }

    public static function make(array $actions = []): static
    {
        return new static($actions);
    }

    public function label(?string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function icon(?string $icon): static
    {
        $this->icon = $icon;

        return $this;
    }

    public function color(?string $color): static
    {
        $this->color = $color;

        return $this;
    }

    public function getActions(): array
    {
        return $this->actions;
    }

    public function setActions(array $actions): static
    {
        $this->actions = $actions;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'type' => 'bulk-action-group',
            'label' => $this->label ?? 'Bulk Actions',
            'icon' => $this->icon,
            'color' => $this->color,
            'actions' => collect($this->actions)
                ->map(fn ($action) => $action->toArray())
                ->all(),
        ];
    }

    public function toInertiaProps(): array
    {
        return $this->toArray();
    }
}
