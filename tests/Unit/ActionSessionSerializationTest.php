<?php

use Laravel\SerializableClosure\SerializableClosure;
use Laravilt\Actions\Action;

it('stores the action closure as a string that survives json session serialization', function () {
    $action = Action::make('greet')->action(fn () => 'hello');

    $method = new ReflectionMethod($action, 'getStandaloneActionToken');
    $method->invoke($action);

    $stored = collect(session()->get('action'))->first();

    // Laravel 13 sessions use JSON serialization by default
    $roundTripped = json_decode(json_encode($stored), true);

    expect($stored)->toBeString()
        ->and($roundTripped)->toBe($stored)
        ->and(Action::restoreActionClosure($roundTripped))->toBeCallable()
        ->and(Action::restoreActionClosure($roundTripped)())->toBe('hello');
});

it('still restores closures stored as SerializableClosure objects', function () {
    $legacy = new SerializableClosure(fn () => 'legacy');

    expect(Action::restoreActionClosure($legacy)())->toBe('legacy');
});
