<?php

use Laravilt\Actions\EditAction;

describe('EditAction Basic Features', function () {
    it('can be instantiated with make method', function () {
        $action = EditAction::make('edit');

        expect($action)->toBeInstanceOf(EditAction::class)
            ->and($action->getName())->toBe('edit');
    });

    it('has default name of edit', function () {
        $action = EditAction::make();

        expect($action->getName())->toBe('edit');
    });

    it('has default icon of Pencil', function () {
        $action = EditAction::make();

        expect($action->getIcon())->toBe('Pencil');
    });

    it('has default color of warning', function () {
        $action = EditAction::make();

        expect($action->getColor())->toBe('warning');
    });

    it('has tooltip set', function () {
        $action = EditAction::make();

        expect($action->getTooltip())->not->toBeNull();
    });

    it('uses GET method for navigation', function () {
        $action = EditAction::make();

        expect($action->getMethod())->toBe('GET');
    });
});

describe('EditAction Visibility for Trashed Records', function () {
    it('is visible for non-trashed records', function () {
        $record = new class
        {
            public function trashed(): bool
            {
                return false;
            }
        };

        $action = EditAction::make();

        expect($action->isHidden($record))->toBeFalse();
    });

    it('is hidden for trashed records (object)', function () {
        $record = new class
        {
            public function trashed(): bool
            {
                return true;
            }
        };

        $action = EditAction::make();

        expect($action->isHidden($record))->toBeTrue();
    });

    it('is hidden for trashed records (array with deleted_at)', function () {
        $record = ['id' => 1, 'deleted_at' => '2024-01-01 00:00:00'];

        $action = EditAction::make();

        expect($action->isHidden($record))->toBeTrue();
    });

    it('is visible for non-trashed records (array without deleted_at)', function () {
        $record = ['id' => 1, 'name' => 'Test'];

        $action = EditAction::make();

        expect($action->isHidden($record))->toBeFalse();
    });

    it('is visible when record is null', function () {
        $action = EditAction::make();

        expect($action->isHidden(null))->toBeFalse();
    });
});

describe('EditAction Serialization', function () {
    it('serializes to array correctly', function () {
        $action = EditAction::make();

        $array = $action->toArray();

        expect($array)->toHaveKey('name')
            ->and($array)->toHaveKey('icon')
            ->and($array)->toHaveKey('color')
            ->and($array)->toHaveKey('method')
            ->and($array['name'])->toBe('edit')
            ->and($array['icon'])->toBe('Pencil')
            ->and($array['color'])->toBe('warning')
            ->and($array['method'])->toBe('GET');
    });
});

describe('EditAction Method Chaining', function () {
    it('supports method chaining', function () {
        $action = EditAction::make()
            ->label('Modify')
            ->icon('pencil-line')
            ->color('primary')
            ->tooltip('Edit this record');

        expect($action)->toBeInstanceOf(EditAction::class);
    });
});
