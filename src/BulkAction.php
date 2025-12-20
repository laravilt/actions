<?php

namespace Laravilt\Actions;

use Closure;

class BulkAction extends Action
{
    protected array $selectedRecords = [];

    protected bool $deselectRecordsAfterCompletion = false;

    protected function setUp(): void
    {
        // Set default modal properties for bulk actions
        $this->requiresConfirmation();
    }

    /**
     * Set whether to deselect records after action completion.
     */
    public function deselectRecordsAfterCompletion(bool $condition = true): static
    {
        $this->deselectRecordsAfterCompletion = $condition;

        return $this;
    }

    /**
     * Get whether to deselect records after completion.
     */
    public function shouldDeselectRecordsAfterCompletion(): bool
    {
        return $this->deselectRecordsAfterCompletion;
    }

    /**
     * Execute the bulk action with selected records.
     */
    public function executeForRecords(array $records, array $data = []): mixed
    {
        $this->selectedRecords = $records;

        if ($this->action === null) {
            return null;
        }

        // Execute the action closure with records
        return call_user_func($this->action, $records, $data);
    }

    /**
     * Get the selected records.
     */
    public function getSelectedRecords(): array
    {
        return $this->selectedRecords;
    }

    /**
     * Override toArray to include bulk-specific properties.
     */
    public function toArray(): array
    {
        $data = parent::toArray();

        $data['isBulkAction'] = true;
        $data['deselectRecordsAfterCompletion'] = $this->deselectRecordsAfterCompletion;

        return $data;
    }
}
