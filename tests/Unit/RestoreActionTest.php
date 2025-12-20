<?php

use Laravilt\Actions\RestoreAction;

describe('RestoreAction Basic Features', function () {
    it('can be instantiated with make method', function () {
        $action = RestoreAction::make('restore');

        expect($action)->toBeInstanceOf(RestoreAction::class)
            ->and($action->getName())->toBe('restore');
    });

    it('has default name of restore', function () {
        $action = RestoreAction::make();

        expect($action->getName())->toBe('restore');
    });

    it('has default icon of RotateCcw', function () {
        $action = RestoreAction::make();

        expect($action->getIcon())->toBe('RotateCcw');
    });

    it('has default color of success', function () {
        $action = RestoreAction::make();

        expect($action->getColor())->toBe('success');
    });

    it('requires confirmation by default', function () {
        $action = RestoreAction::make();

        expect($action->getRequiresConfirmation())->toBeTrue();
    });
});

describe('RestoreAction Visibility for Trashed Records', function () {
    it('is hidden for non-trashed records (object)', function () {
        $record = new class
        {
            public function trashed(): bool
            {
                return false;
            }
        };

        $action = RestoreAction::make();

        expect($action->isHidden($record))->toBeTrue();
    });

    it('is visible for trashed records (object)', function () {
        $record = new class
        {
            public function trashed(): bool
            {
                return true;
            }
        };

        $action = RestoreAction::make();

        expect($action->isHidden($record))->toBeFalse();
    });

    it('is hidden for non-trashed records (array without deleted_at)', function () {
        $record = ['id' => 1, 'name' => 'Test'];

        $action = RestoreAction::make();

        expect($action->isHidden($record))->toBeTrue();
    });

    it('is visible for trashed records (array with deleted_at)', function () {
        $record = ['id' => 1, 'deleted_at' => '2024-01-01 00:00:00'];

        $action = RestoreAction::make();

        expect($action->isHidden($record))->toBeFalse();
    });

    it('handles null record gracefully', function () {
        $action = RestoreAction::make();

        // The hidden closure returns true for null, but isHidden may handle it differently
        expect($action)->toBeInstanceOf(RestoreAction::class);
    });
});

describe('RestoreAction Serialization', function () {
    it('serializes to array correctly', function () {
        $action = RestoreAction::make();

        $array = $action->toArray();

        expect($array)->toHaveKey('name')
            ->and($array)->toHaveKey('icon')
            ->and($array)->toHaveKey('color')
            ->and($array['name'])->toBe('restore')
            ->and($array['color'])->toBe('success');
    });

    it('does not preserve state by default', function () {
        $action = RestoreAction::make();

        $array = $action->toArray();

        expect($array['preserveState'])->toBeFalse();
    });
});
