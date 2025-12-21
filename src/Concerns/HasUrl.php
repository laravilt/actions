<?php

namespace Laravilt\Actions\Concerns;

use Closure;

trait HasUrl
{
    protected string|Closure|null $url = null;

    protected bool $shouldOpenUrlInNewTab = false;

    public function url(string|Closure|null $url, bool $shouldOpenInNewTab = false): static
    {
        $this->url = $url;
        $this->shouldOpenUrlInNewTab = $shouldOpenInNewTab;

        return $this;
    }

    public function openUrlInNewTab(bool $condition = true): static
    {
        $this->shouldOpenUrlInNewTab = $condition;

        return $this;
    }

    public function getUrl(mixed $record = null): ?string
    {
        if ($this->url instanceof Closure) {
            if ($record === null) {
                return null;
            }

            return ($this->url)($record);
        }

        return $this->url;
    }

    /**
     * Check if the URL is callable (closure-based).
     */
    public function hasCallableUrl(): bool
    {
        return $this->url instanceof Closure;
    }

    public function shouldOpenUrlInNewTab(): bool
    {
        return $this->shouldOpenUrlInNewTab;
    }
}
