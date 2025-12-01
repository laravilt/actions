<?php

use Laravilt\Actions\Action;

describe('Action Basic Features', function () {
    it('can be instantiated with make method', function () {
        $action = Action::make('test-action');

        expect($action)->toBeInstanceOf(Action::class)
            ->and($action->getName())->toBe('test-action');
    });

    it('can set and get name', function () {
        $action = Action::make()->name('my-action');

        expect($action->getName())->toBe('my-action');
    });

    it('can set and get label', function () {
        $action = Action::make('test')->label('Test Action');

        expect($action->getLabel())->toBe('Test Action');
    });

    it('can set and get color', function () {
        $action = Action::make('test')->color('primary');

        expect($action->getColor())->toBe('primary');
    });

    it('can set and get icon', function () {
        $action = Action::make('test')->icon('star');

        expect($action->getIcon())->toBe('star');
    });


    it('can set and get tooltip', function () {
        $action = Action::make('test')->tooltip('Click me');

        expect($action->getTooltip())->toBe('Click me');
    });

    it('can set disabled state', function () {
        $action = Action::make('test')->disabled();

        expect($action->isDisabled())->toBeTrue();
    });

    it('can set outlined state', function () {
        $action = Action::make('test')->outlined();

        expect($action->isOutlined())->toBeTrue();
    });

    it('can set size', function () {
        $action = Action::make('test')->size('sm');

        expect($action->getSize())->toBe('sm');
    });
});

describe('Action Variants', function () {
    it('can be set as button variant', function () {
        $action = Action::make('test')->button();

        expect($action->getVariant())->toBe('button');
    });

    it('can be set as icon variant', function () {
        $action = Action::make('test')->iconButton();

        expect($action->getVariant())->toBe('icon');
    });

    it('can be set as link variant', function () {
        $action = Action::make('test')->link();

        expect($action->getVariant())->toBe('link');
    });
});

describe('Action URLs', function () {
    it('can set and get url', function () {
        $action = Action::make('test')->url('https://example.com');

        expect($action->getUrl())->toBe('https://example.com');
    });

    it('can set open url in new tab', function () {
        $action = Action::make('test')
            ->url('https://example.com')
            ->openUrlInNewTab();

        expect($action->shouldOpenUrlInNewTab())->toBeTrue();
    });
});

describe('Action Modals', function () {
    it('can set requires confirmation', function () {
        $action = Action::make('test')->requiresConfirmation();

        expect($action->getRequiresConfirmation())->toBeTrue();
    });

    it('can set modal heading', function () {
        $action = Action::make('test')
            ->requiresConfirmation()
            ->modalHeading('Confirm Action');

        expect($action->getModalHeading())->toBe('Confirm Action');
    });

    it('can set modal description', function () {
        $action = Action::make('test')
            ->requiresConfirmation()
            ->modalDescription('Are you sure?');

        expect($action->getModalDescription())->toBe('Are you sure?');
    });

    it('can set modal icon', function () {
        $action = Action::make('test')
            ->requiresConfirmation()
            ->modalIcon('alert-triangle');

        expect($action->getModalIcon())->toBe('alert-triangle');
    });

    it('can set modal icon color', function () {
        $action = Action::make('test')
            ->requiresConfirmation()
            ->modalIconColor('danger');

        expect($action->getModalIconColor())->toBe('danger');
    });

    it('can set modal submit action label', function () {
        $action = Action::make('test')
            ->requiresConfirmation()
            ->modalSubmitActionLabel('Yes, Delete');

        expect($action->getModalSubmitActionLabel())->toBe('Yes, Delete');
    });

    it('can set modal cancel action label', function () {
        $action = Action::make('test')
            ->requiresConfirmation()
            ->modalCancelActionLabel('No, Cancel');

        expect($action->getModalCancelActionLabel())->toBe('No, Cancel');
    });

    it('can set modal form schema', function () {
        $schema = [
            ['type' => 'text', 'name' => 'name'],
            ['type' => 'email', 'name' => 'email'],
        ];

        $action = Action::make('test')
            ->requiresConfirmation()
            ->schema($schema);

        expect($action->getModalFormSchema())->toBe($schema);
    });

    it('can set requires password', function () {
        $action = Action::make('test')->requiresPassword();

        expect($action->getRequiresPassword())->toBeTrue()
            ->and($action->getRequiresConfirmation())->toBeTrue();
    });
});

describe('Action Execution', function () {
    it('can execute action with closure', function () {
        $executed = false;

        $action = Action::make('test')
            ->action(function () use (&$executed) {
                $executed = true;
                return 'success';
            });

        $result = $action->execute();

        expect($executed)->toBeTrue()
            ->and($result)->toBe('success');
    });

    it('can execute action with record', function () {
        $record = (object) ['id' => 1, 'name' => 'Test'];
        $passedRecord = null;

        $action = Action::make('test')
            ->action(function ($rec) use (&$passedRecord) {
                $passedRecord = $rec;
                return $rec->name;
            });

        $result = $action->execute($record);

        expect($passedRecord)->toBe($record)
            ->and($result)->toBe('Test');
    });

    it('can execute action with data', function () {
        $passedData = null;

        $action = Action::make('test')
            ->action(function ($record, $data) use (&$passedData) {
                $passedData = $data;
                return $data;
            });

        $data = ['key' => 'value'];
        $result = $action->execute(null, $data);

        expect($passedData)->toBe($data)
            ->and($result)->toBe($data);
    });

    it('returns null if no action closure is set', function () {
        $action = Action::make('test');

        $result = $action->execute();

        expect($result)->toBeNull();
    });
});

describe('Action Authorization', function () {
    it('can authorize by default', function () {
        $action = Action::make('test');

        expect($action->canAuthorize())->toBeTrue();
    });

    it('can set authorize closure', function () {
        $action = Action::make('test')
            ->authorize(fn () => false);

        expect($action->canAuthorize())->toBeFalse();
    });

    it('can authorize with record', function () {
        $record = (object) ['can_edit' => true];

        $action = Action::make('test')
            ->authorize(fn ($rec) => $rec->can_edit);

        expect($action->canAuthorize($record))->toBeTrue();
    });
});

describe('Action Visibility', function () {
    it('is visible by default', function () {
        $action = Action::make('test');

        expect($action->isHidden())->toBeFalse();
    });

    it('can be hidden', function () {
        $action = Action::make('test')->hidden();

        expect($action->isHidden())->toBeTrue();
    });

    it('can be hidden conditionally', function () {
        $action = Action::make('test')->hidden(fn () => true);

        expect($action->isHidden())->toBeTrue();
    });
});

describe('Action Component Metadata', function () {
    it('can set component metadata', function () {
        $action = Action::make('test')
            ->component('App\\Panel\\Resources\\UserResource', 1, 'edit');

        $token = $action->getActionToken();

        expect($token)->toBeString();

        // Decrypt and verify token
        $payload = \Illuminate\Support\Facades\Crypt::decrypt($token);

        expect($payload)->toHaveKey('component')
            ->and($payload)->toHaveKey('id')
            ->and($payload)->toHaveKey('action')
            ->and($payload)->toHaveKey('panel')
            ->and($payload['component'])->toBe('App\\Panel\\Resources\\UserResource')
            ->and($payload['id'])->toBe(1)
            ->and($payload['action'])->toBe('test')
            ->and($payload['panel'])->toBe('edit');
    });

    it('generates different tokens for different components', function () {
        $action1 = Action::make('test')->component('Component1', 1);
        $action2 = Action::make('test')->component('Component2', 2);

        expect($action1->getActionToken())->not->toBe($action2->getActionToken());
    });
});

describe('Action Serialization', function () {
    it('serializes to array correctly', function () {
        $action = Action::make('test-action')
            ->label('Test Label')
            ->icon('star')
            ->color('primary')
            ->tooltip('Test tooltip')
            ->button()
            ->size('sm');

        $array = $action->toArray();

        expect($array)->toHaveKey('name')
            ->and($array)->toHaveKey('label')
            ->and($array)->toHaveKey('icon')
            ->and($array)->toHaveKey('color')
            ->and($array)->toHaveKey('tooltip')
            ->and($array)->toHaveKey('variant')
            ->and($array)->toHaveKey('size')
            ->and($array['name'])->toBe('test-action')
            ->and($array['label'])->toBe('Test Label')
            ->and($array['icon'])->toBe('star')
            ->and($array['color'])->toBe('primary')
            ->and($array['tooltip'])->toBe('Test tooltip')
            ->and($array['variant'])->toBe('button')
            ->and($array['size'])->toBe('sm');
    });

    it('serializes modal properties', function () {
        $action = Action::make('test')
            ->requiresConfirmation()
            ->modalHeading('Confirm')
            ->modalDescription('Are you sure?')
            ->modalIcon('alert')
            ->modalIconColor('danger');

        $array = $action->toArray();

        expect($array['requiresConfirmation'])->toBeTrue()
            ->and($array['modalHeading'])->toBe('Confirm')
            ->and($array['modalDescription'])->toBe('Are you sure?')
            ->and($array['modalIcon'])->toBe('alert')
            ->and($array['modalIconColor'])->toBe('danger');
    });

    it('includes hasAction flag', function () {
        $actionWithClosure = Action::make('test')
            ->action(fn () => 'executed');

        $actionWithoutClosure = Action::make('test');

        expect($actionWithClosure->toArray()['hasAction'])->toBeTrue()
            ->and($actionWithoutClosure->toArray()['hasAction'])->toBeFalse();
    });
});

describe('Action Method Chaining', function () {
    it('supports method chaining', function () {
        $action = Action::make('test')
            ->label('Test')
            ->icon('star')
            ->color('primary')
            ->button()
            ->size('sm')
            ->tooltip('Tooltip')
            ->requiresConfirmation();

        expect($action)->toBeInstanceOf(Action::class);
    });
});
