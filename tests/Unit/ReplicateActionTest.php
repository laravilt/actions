<?php

use Laravilt\Actions\ReplicateAction;

describe('ReplicateAction Basic Features', function () {
    it('can be instantiated with make method', function () {
        $action = ReplicateAction::make('replicate');

        expect($action)->toBeInstanceOf(ReplicateAction::class)
            ->and($action->getName())->toBe('replicate');
    });

    it('has default name of replicate', function () {
        $action = ReplicateAction::make();

        expect($action->getName())->toBe('replicate');
    });

    it('has default icon of Copy', function () {
        $action = ReplicateAction::make();

        expect($action->getIcon())->toBe('Copy');
    });

    it('has default color of gray', function () {
        $action = ReplicateAction::make();

        expect($action->getColor())->toBe('gray');
    });

    it('requires confirmation by default', function () {
        $action = ReplicateAction::make();

        expect($action->getRequiresConfirmation())->toBeTrue();
    });

    it('has modal heading set', function () {
        $action = ReplicateAction::make();

        expect($action->getModalHeading())->not->toBeNull();
    });

    it('has modal description set', function () {
        $action = ReplicateAction::make();

        expect($action->getModalDescription())->not->toBeNull();
    });
});

describe('ReplicateAction Exclude Attributes', function () {
    it('has no excluded attributes by default', function () {
        $action = ReplicateAction::make();

        expect($action->getExcludedAttributes())->toBeEmpty();
    });

    it('can set excluded attributes', function () {
        $action = ReplicateAction::make()
            ->excludeAttributes(['id', 'created_at', 'updated_at']);

        expect($action->getExcludedAttributes())->toBe(['id', 'created_at', 'updated_at']);
    });
});

describe('ReplicateAction Callbacks', function () {
    it('can set before replica saved callback', function () {
        $action = ReplicateAction::make()
            ->beforeReplicaSaved(fn ($replica, $original) => null);

        expect($action)->toBeInstanceOf(ReplicateAction::class);
    });

    it('can set after replica saved callback', function () {
        $action = ReplicateAction::make()
            ->afterReplicaSaved(fn ($replica, $original) => null);

        expect($action)->toBeInstanceOf(ReplicateAction::class);
    });

    it('can set success redirect url', function () {
        $action = ReplicateAction::make()
            ->successRedirectUrl(fn ($replica) => '/records/'.$replica->id.'/edit');

        expect($action)->toBeInstanceOf(ReplicateAction::class);
    });
});

describe('ReplicateAction Execution', function () {
    it('returns null if record is not a Model', function () {
        $action = ReplicateAction::make();

        $result = $action->execute((object) ['id' => 1]);

        expect($result)->toBeNull();
    });

    it('returns null for array record', function () {
        $action = ReplicateAction::make();

        $result = $action->execute(['id' => 1, 'name' => 'Test']);

        expect($result)->toBeNull();
    });
});

describe('ReplicateAction Serialization', function () {
    it('serializes to array correctly', function () {
        $action = ReplicateAction::make();

        $array = $action->toArray();

        expect($array)->toHaveKey('name')
            ->and($array)->toHaveKey('icon')
            ->and($array)->toHaveKey('color')
            ->and($array)->toHaveKey('requiresConfirmation')
            ->and($array['name'])->toBe('replicate')
            ->and($array['icon'])->toBe('Copy')
            ->and($array['requiresConfirmation'])->toBeTrue();
    });
});

describe('ReplicateAction Method Chaining', function () {
    it('supports method chaining', function () {
        $action = ReplicateAction::make()
            ->label('Duplicate')
            ->icon('copy-plus')
            ->color('success')
            ->excludeAttributes(['id', 'slug'])
            ->beforeReplicaSaved(fn ($replica, $original) => $replica->name = $original->name.' (Copy)')
            ->afterReplicaSaved(fn ($replica, $original) => null)
            ->modalHeading('Duplicate Record')
            ->modalDescription('Create a copy of this record?');

        expect($action)->toBeInstanceOf(ReplicateAction::class);
    });
});
