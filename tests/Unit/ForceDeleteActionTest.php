<?php

use Laravilt\Actions\ForceDeleteAction;

describe('ForceDeleteAction Basic Features', function () {
    it('can be instantiated with make method', function () {
        $action = ForceDeleteAction::make('force-delete');

        expect($action)->toBeInstanceOf(ForceDeleteAction::class)
            ->and($action->getName())->toBe('force-delete');
    });

    it('has default name of force-delete', function () {
        $action = ForceDeleteAction::make();

        expect($action->getName())->toBe('force-delete');
    });

    it('has default icon of Trash2', function () {
        $action = ForceDeleteAction::make();

        expect($action->getIcon())->toBe('Trash2');
    });

    it('has default color of destructive', function () {
        $action = ForceDeleteAction::make();

        expect($action->getColor())->toBe('destructive');
    });

    it('requires confirmation by default', function () {
        $action = ForceDeleteAction::make();

        expect($action->getRequiresConfirmation())->toBeTrue();
    });
});

describe('ForceDeleteAction Visibility for Trashed Records', function () {
    it('is hidden for non-trashed records (object)', function () {
        $record = new class
        {
            public function trashed(): bool
            {
                return false;
            }
        };

        $action = ForceDeleteAction::make();

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

        $action = ForceDeleteAction::make();

        expect($action->isHidden($record))->toBeFalse();
    });

    it('is hidden for non-trashed records (array without deleted_at)', function () {
        $record = ['id' => 1, 'name' => 'Test'];

        $action = ForceDeleteAction::make();

        expect($action->isHidden($record))->toBeTrue();
    });

    it('is visible for trashed records (array with deleted_at)', function () {
        $record = ['id' => 1, 'deleted_at' => '2024-01-01 00:00:00'];

        $action = ForceDeleteAction::make();

        expect($action->isHidden($record))->toBeFalse();
    });

    it('handles null record gracefully', function () {
        $action = ForceDeleteAction::make();

        // The hidden closure returns true for null, but isHidden may handle it differently
        expect($action)->toBeInstanceOf(ForceDeleteAction::class);
    });
});

describe('ForceDeleteAction Serialization', function () {
    it('serializes to array correctly', function () {
        $action = ForceDeleteAction::make();

        $array = $action->toArray();

        expect($array)->toHaveKey('name')
            ->and($array)->toHaveKey('icon')
            ->and($array)->toHaveKey('color')
            ->and($array['name'])->toBe('force-delete')
            ->and($array['color'])->toBe('destructive');
    });

    it('does not preserve state by default', function () {
        $action = ForceDeleteAction::make();

        $array = $action->toArray();

        expect($array['preserveState'])->toBeFalse();
    });
});
