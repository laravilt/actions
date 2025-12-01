<?php

namespace Laravilt\Actions\Concerns;

use Closure;

trait CanBeHidden
{
    protected bool|Closure $isHidden = false;

    public function hidden(bool|Closure $condition = true): static
    {
        $this->isHidden = $condition;

        return $this;
    }

    public function visible(bool|Closure $condition = true): static
    {
        $this->isHidden = ! $condition;

        return $this;
    }

    public function isHidden(): bool
    {
        return (bool) $this->evaluate($this->isHidden);
    }

    public function isVisible(): bool
    {
        return ! $this->isHidden();
    }

    protected function evaluate(mixed $value): mixed
    {
        if ($value instanceof Closure) {
            return $value();
        }

        return $value;
    }
}
