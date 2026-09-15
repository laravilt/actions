<?php

use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Laravilt\Actions\Action;

beforeEach(function () {
    File::deleteDirectory(app_path('Actions'));
});

afterEach(function () {
    File::deleteDirectory(app_path('Actions'));
});

/**
 * Generate an action class with a unique name and load it.
 */
function generateAction(array $options = []): string
{
    $name = 'Generated'.Str::studly(Str::random(10)).'Action';

    test()->artisan('make:action', ['name' => $name, ...$options])->assertSuccessful();

    $path = app_path("Actions/{$name}.php");
    expect($path)->toBeFile();

    // Valid PHP: must lint and load without errors
    exec(escapeshellarg(PHP_BINARY).' -l '.escapeshellarg($path), $output, $exitCode);
    expect($exitCode)->toBe(0);

    require_once $path;

    $class = "App\\Actions\\{$name}";
    expect(class_exists($class))->toBeTrue();

    return $class;
}

/**
 * Wrap a generated class so handle() records its arguments.
 */
function spyOnHandle(string $class): Action
{
    $spy = eval('return new class extends '.$class.' {
        public array $calls = [];

        public function handle(mixed $record = null, array $data = []): mixed
        {
            $this->calls[] = [$record, $data];

            return "handled";
        }
    };');

    return $spy;
}

it('generates an action whose closure calls handle()', function (array $options) {
    $class = generateAction($options);

    $action = $class::make();
    expect($action)->toBeInstanceOf(Action::class)
        ->and($action->getAction())->not->toBeNull()
        ->and($action->execute())->toBeNull();

    $spy = spyOnHandle($class);
    expect($spy->execute('record', ['name' => 'Jane']))->toBe('handled')
        ->and($spy->calls)->toBe([['record', ['name' => 'Jane']]]);

    // No --auth: action is not restricted
    expect($action->canAuthorize())->toBeTrue();
    expect(file_get_contents((new ReflectionClass($class))->getFileName()))
        ->not->toContain('{{')
        ->not->toContain('authorizeAction');
})->with([
    'default' => [[]],
    'modal' => [['--modal' => true]],
    'form' => [['--form' => true]],
]);

it('generates working authorization with --auth', function (array $options) {
    $class = generateAction(['--auth' => true, ...$options]);

    expect(file_get_contents((new ReflectionClass($class))->getFileName()))
        ->toContain('authorizeAction');

    expect($class::make()->canAuthorize())->toBeFalse();

    $this->actingAs(new User);

    expect($class::make()->canAuthorize())->toBeTrue();
})->with([
    'default' => [[]],
    'modal' => [['--modal' => true]],
    'form' => [['--form' => true]],
]);
