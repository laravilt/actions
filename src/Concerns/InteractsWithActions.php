<?php

namespace Laravilt\Actions\Concerns;

use Laravilt\Actions\Action;

trait InteractsWithActions
{
    protected array $cachedActions = [];

    /**
     * Get the actions available on the page.
     */
    protected function getActions(): array
    {
        return [];
    }

    /**
     * Get the header actions available on the page.
     */
    public function getHeaderActions(): array
    {
        return $this->configureActions($this->getActions());
    }

    /**
     * Get the footer actions available on the page.
     */
    protected function getFooterActions(): array
    {
        return $this->configureActions([]);
    }

    /**
     * Configure actions with component metadata automatically.
     */
    protected function configureActions(array $actions): array
    {
        // Get panel ID if available
        $panelId = null;
        if (method_exists($this, 'getPanel')) {
            $panel = $this->getPanel();
            $panelId = $panel?->getId();
        }

        // Configure each action with component metadata
        foreach ($actions as $action) {
            if ($action instanceof Action) {
                // Only set component if not already set
                $actionArray = $action->toArray();
                if (empty($actionArray['actionToken'])) {
                    $action->component(static::class, null, $panelId);
                }
            }
        }

        return $actions;
    }
}
