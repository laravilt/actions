<?php

namespace Laravilt\Actions\Concerns;

use Closure;

trait CanBeHidden
{
    protected bool|Closure $isHidden = false;

    /**
     * Whether the visibility condition requires a record.
     */
    protected bool $visibilityRequiresRecord = false;

    public function hidden(bool|Closure $condition = true): static
    {
        $this->isHidden = $condition;
        $this->detectRecordRequirement($condition);

        return $this;
    }

    public function visible(bool|Closure $condition = true): static
    {
        if ($condition instanceof Closure) {
            // Invert the closure result for hidden check
            $this->isHidden = fn ($record = null) => ! $condition($record);
            $this->detectRecordRequirement($condition);
        } else {
            $this->isHidden = ! $condition;
        }

        return $this;
    }

    /**
     * Detect if the closure requires a record parameter.
     */
    protected function detectRecordRequirement(mixed $condition): void
    {
        if ($condition instanceof Closure) {
            $reflection = new \ReflectionFunction($condition);
            $params = $reflection->getParameters();
            if (count($params) > 0 && $params[0]->getName() === 'record') {
                $this->visibilityRequiresRecord = true;
            }
        }
    }

    /**
     * Check if visibility requires record.
     */
    public function visibilityRequiresRecord(): bool
    {
        return $this->visibilityRequiresRecord;
    }

    public function isHidden(mixed $record = null): bool
    {
        return (bool) $this->evaluate($this->isHidden, $record);
    }

    public function isVisible(mixed $record = null): bool
    {
        return ! $this->isHidden($record);
    }

    protected function evaluate(mixed $value, mixed $record = null): mixed
    {
        if ($value instanceof Closure) {
            // If the closure needs a record but none provided, default to visible
            if ($this->visibilityRequiresRecord && $record === null) {
                return false; // Not hidden by default when no record
            }

            return $value($record);
        }

        return $value;
    }
}
