<?php

use Laravilt\Actions\BulkAction;

describe('BulkAction Basic Features', function () {
    it('can be instantiated with make method', function () {
        $action = BulkAction::make('bulk-delete');

        expect($action)->toBeInstanceOf(BulkAction::class)
            ->and($action->getName())->toBe('bulk-delete');
    });

    it('requires confirmation by default', function () {
        $action = BulkAction::make('bulk-action');

        expect($action->getRequiresConfirmation())->toBeTrue();
    });
});

describe('BulkAction Deselect Records', function () {
    it('does not deselect records by default', function () {
        $action = BulkAction::make('bulk-action');

        expect($action->shouldDeselectRecordsAfterCompletion())->toBeFalse();
    });

    it('can enable deselect records after completion', function () {
        $action = BulkAction::make('bulk-action')
            ->deselectRecordsAfterCompletion();

        expect($action->shouldDeselectRecordsAfterCompletion())->toBeTrue();
    });

    it('can disable deselect records after completion', function () {
        $action = BulkAction::make('bulk-action')
            ->deselectRecordsAfterCompletion(false);

        expect($action->shouldDeselectRecordsAfterCompletion())->toBeFalse();
    });
});

describe('BulkAction Execution', function () {
    it('can execute action with records', function () {
        $records = [
            (object) ['id' => 1, 'name' => 'Test 1'],
            (object) ['id' => 2, 'name' => 'Test 2'],
        ];
        $passedRecords = null;

        $action = BulkAction::make('bulk-action')
            ->action(function ($recs) use (&$passedRecords) {
                $passedRecords = $recs;

                return count($recs);
            });

        $result = $action->executeForRecords($records);

        expect($passedRecords)->toBe($records)
            ->and($result)->toBe(2);
    });

    it('can execute action with records and data', function () {
        $records = [(object) ['id' => 1]];
        $passedData = null;

        $action = BulkAction::make('bulk-action')
            ->action(function ($recs, $data) use (&$passedData) {
                $passedData = $data;

                return $data;
            });

        $data = ['status' => 'active'];
        $result = $action->executeForRecords($records, $data);

        expect($passedData)->toBe($data)
            ->and($result)->toBe($data);
    });

    it('returns null if no action closure is set', function () {
        $action = BulkAction::make('bulk-action');
        $records = [(object) ['id' => 1]];

        $result = $action->executeForRecords($records);

        expect($result)->toBeNull();
    });

    it('can get selected records after execution', function () {
        $records = [
            (object) ['id' => 1],
            (object) ['id' => 2],
        ];

        $action = BulkAction::make('bulk-action')
            ->action(fn ($recs) => $recs);

        $action->executeForRecords($records);

        expect($action->getSelectedRecords())->toBe($records);
    });
});

describe('BulkAction Serialization', function () {
    it('serializes to array with isBulkAction flag', function () {
        $action = BulkAction::make('bulk-delete');

        $array = $action->toArray();

        expect($array)->toHaveKey('isBulkAction')
            ->and($array['isBulkAction'])->toBeTrue();
    });

    it('includes deselectRecordsAfterCompletion in serialization', function () {
        $action = BulkAction::make('bulk-delete')
            ->deselectRecordsAfterCompletion();

        $array = $action->toArray();

        expect($array)->toHaveKey('deselectRecordsAfterCompletion')
            ->and($array['deselectRecordsAfterCompletion'])->toBeTrue();
    });
});

describe('BulkAction Method Chaining', function () {
    it('supports method chaining', function () {
        $action = BulkAction::make('bulk-action')
            ->label('Delete Selected')
            ->icon('trash')
            ->color('danger')
            ->deselectRecordsAfterCompletion()
            ->modalHeading('Confirm Bulk Delete')
            ->modalDescription('Are you sure you want to delete all selected records?')
            ->action(fn ($records) => $records);

        expect($action)->toBeInstanceOf(BulkAction::class);
    });
});
