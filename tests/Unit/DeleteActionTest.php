<?php

use Laravilt\Actions\DeleteAction;

describe('DeleteAction Basic Features', function () {
    it('can be instantiated with make method', function () {
        $action = DeleteAction::make('delete');

        expect($action)->toBeInstanceOf(DeleteAction::class)
            ->and($action->getName())->toBe('delete');
    });

    it('has default name of delete', function () {
        $action = DeleteAction::make();

        expect($action->getName())->toBe('delete');
    });

    it('has default icon of Trash2', function () {
        $action = DeleteAction::make();

        expect($action->getIcon())->toBe('Trash2');
    });

    it('has default color of destructive', function () {
        $action = DeleteAction::make();

        expect($action->getColor())->toBe('destructive');
    });

    it('requires confirmation by default', function () {
        $action = DeleteAction::make();

        expect($action->getRequiresConfirmation())->toBeTrue();
    });

    it('has tooltip set', function () {
        $action = DeleteAction::make();

        expect($action->getTooltip())->not->toBeNull();
    });
});

describe('DeleteAction Visibility for Trashed Records', function () {
    it('is visible for non-trashed records', function () {
        $record = new class
        {
            public function trashed(): bool
            {
                return false;
            }
        };

        $action = DeleteAction::make();

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

        $action = DeleteAction::make();

        expect($action->isHidden($record))->toBeTrue();
    });

    it('is hidden for trashed records (array with deleted_at)', function () {
        $record = ['id' => 1, 'deleted_at' => '2024-01-01 00:00:00'];

        $action = DeleteAction::make();

        expect($action->isHidden($record))->toBeTrue();
    });

    it('is visible for non-trashed records (array without deleted_at)', function () {
        $record = ['id' => 1, 'name' => 'Test'];

        $action = DeleteAction::make();

        expect($action->isHidden($record))->toBeFalse();
    });

    it('is visible when record is null', function () {
        $action = DeleteAction::make();

        expect($action->isHidden(null))->toBeFalse();
    });
});

describe('DeleteAction Serialization', function () {
    it('serializes to array correctly', function () {
        $action = DeleteAction::make();

        $array = $action->toArray();

        expect($array)->toHaveKey('name')
            ->and($array)->toHaveKey('icon')
            ->and($array)->toHaveKey('color')
            ->and($array)->toHaveKey('requiresConfirmation')
            ->and($array['name'])->toBe('delete')
            ->and($array['color'])->toBe('destructive')
            ->and($array['requiresConfirmation'])->toBeTrue();
    });

    it('does not preserve state by default', function () {
        $action = DeleteAction::make();

        $array = $action->toArray();

        expect($array['preserveState'])->toBeFalse();
    });
});

describe('DeleteAction Method Chaining', function () {
    it('supports method chaining', function () {
        $action = DeleteAction::make()
            ->label('Remove')
            ->icon('trash')
            ->color('danger')
            ->modalHeading('Confirm Deletion')
            ->modalDescription('Are you sure you want to delete this record?');

        expect($action)->toBeInstanceOf(DeleteAction::class);
    });
});
