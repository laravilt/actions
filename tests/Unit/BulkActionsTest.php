<?php

use Laravilt\Actions\DeleteBulkAction;
use Laravilt\Actions\ForceDeleteBulkAction;
use Laravilt\Actions\RestoreBulkAction;

describe('DeleteBulkAction', function () {
    it('can be instantiated with make method', function () {
        $action = DeleteBulkAction::make('bulk-delete');

        expect($action)->toBeInstanceOf(DeleteBulkAction::class)
            ->and($action->getName())->toBe('bulk-delete');
    });

    it('has default name of delete', function () {
        $action = DeleteBulkAction::make();

        expect($action->getName())->toBe('delete');
    });

    it('has default icon of Trash2', function () {
        $action = DeleteBulkAction::make();

        expect($action->getIcon())->toBe('Trash2');
    });

    it('has default color of destructive', function () {
        $action = DeleteBulkAction::make();

        expect($action->getColor())->toBe('destructive');
    });

    it('requires confirmation by default', function () {
        $action = DeleteBulkAction::make();

        expect($action->getRequiresConfirmation())->toBeTrue();
    });

    it('deselects records after completion by default', function () {
        $action = DeleteBulkAction::make();

        expect($action->shouldDeselectRecordsAfterCompletion())->toBeTrue();
    });

    it('can set model class', function () {
        $action = DeleteBulkAction::make()
            ->model('App\\Models\\Customer');

        expect($action->getModel())->toBe('App\\Models\\Customer');
    });

    it('includes isBulkAction flag in serialization', function () {
        $action = DeleteBulkAction::make();

        $array = $action->toArray();

        expect($array['isBulkAction'])->toBeTrue();
    });
});

describe('ForceDeleteBulkAction', function () {
    it('can be instantiated with make method', function () {
        $action = ForceDeleteBulkAction::make();

        expect($action)->toBeInstanceOf(ForceDeleteBulkAction::class);
    });

    it('has default name of force-delete', function () {
        $action = ForceDeleteBulkAction::make();

        expect($action->getName())->toBe('force-delete');
    });

    it('has default color of destructive', function () {
        $action = ForceDeleteBulkAction::make();

        expect($action->getColor())->toBe('destructive');
    });

    it('can set model class', function () {
        $action = ForceDeleteBulkAction::make()
            ->model('App\\Models\\Customer');

        expect($action->getModel())->toBe('App\\Models\\Customer');
    });
});

describe('RestoreBulkAction', function () {
    it('can be instantiated with make method', function () {
        $action = RestoreBulkAction::make();

        expect($action)->toBeInstanceOf(RestoreBulkAction::class);
    });

    it('has default name of restore', function () {
        $action = RestoreBulkAction::make();

        expect($action->getName())->toBe('restore');
    });

    it('has default color of success', function () {
        $action = RestoreBulkAction::make();

        expect($action->getColor())->toBe('success');
    });

    it('can set model class', function () {
        $action = RestoreBulkAction::make()
            ->model('App\\Models\\Customer');

        expect($action->getModel())->toBe('App\\Models\\Customer');
    });
});
