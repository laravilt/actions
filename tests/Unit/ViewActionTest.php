<?php

use Laravilt\Actions\ViewAction;

describe('ViewAction Basic Features', function () {
    it('can be instantiated with make method', function () {
        $action = ViewAction::make('view');

        expect($action)->toBeInstanceOf(ViewAction::class)
            ->and($action->getName())->toBe('view');
    });

    it('has default name of view', function () {
        $action = ViewAction::make();

        expect($action->getName())->toBe('view');
    });

    it('has default icon of Eye', function () {
        $action = ViewAction::make();

        expect($action->getIcon())->toBe('Eye');
    });

    it('has default color of secondary', function () {
        $action = ViewAction::make();

        expect($action->getColor())->toBe('secondary');
    });

    it('has tooltip set', function () {
        $action = ViewAction::make();

        expect($action->getTooltip())->not->toBeNull();
    });

    it('uses GET method for navigation', function () {
        $action = ViewAction::make();

        expect($action->getMethod())->toBe('GET');
    });
});

describe('ViewAction Serialization', function () {
    it('serializes to array correctly', function () {
        $action = ViewAction::make();

        $array = $action->toArray();

        expect($array)->toHaveKey('name')
            ->and($array)->toHaveKey('icon')
            ->and($array)->toHaveKey('color')
            ->and($array)->toHaveKey('method')
            ->and($array['name'])->toBe('view')
            ->and($array['icon'])->toBe('Eye')
            ->and($array['color'])->toBe('secondary')
            ->and($array['method'])->toBe('GET');
    });
});

describe('ViewAction Method Chaining', function () {
    it('supports method chaining', function () {
        $action = ViewAction::make()
            ->label('View Details')
            ->icon('eye')
            ->color('info')
            ->tooltip('View record details');

        expect($action)->toBeInstanceOf(ViewAction::class);
    });
});
