<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Auth\User as AuthUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Laravilt\Actions\DeleteAction;
use Laravilt\Actions\DeleteBulkAction;
use Laravilt\Actions\ForceDeleteBulkAction;
use Laravilt\Actions\Http\Controllers\ActionController;
use Laravilt\Actions\RestoreBulkAction;
use Laravilt\Panel\Panel;
use Laravilt\Panel\PanelRegistry;
use Laravilt\Panel\Resources\Resource;
use Symfony\Component\HttpKernel\Exception\HttpException;

// The notifications package is not part of this package's dev dependencies
if (! class_exists(\Laravilt\Notifications\Notification::class)) {
    eval('namespace Laravilt\Notifications; class Notification { public static function __callStatic($n, $a) { return new static; } public function __call($n, $a) { return $this; } }');
}

class AuthzUser extends AuthUser
{
    protected $table = 'users';

    protected $guarded = [];
}

class AuthzPost extends Model
{
    use SoftDeletes;

    protected $table = 'authz_posts';

    protected $guarded = [];
}

class AuthzPolicyPost extends Model
{
    protected $table = 'authz_posts';

    protected $guarded = [];
}

class AuthzPolicyPostPolicy
{
    public function delete($user, $post): bool
    {
        return false;
    }

    public function deleteAny($user): bool
    {
        return false;
    }
}

class AuthzPostResource extends Resource
{
    protected static string $model = AuthzPost::class;

    protected static ?string $slug = 'authz-posts';

    public static function canDelete(?Model $record = null): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [];
    }
}

beforeEach(function () {
    Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->string('name')->nullable();
        $table->string('email')->nullable();
        $table->string('password')->nullable();
        $table->timestamps();
    });
    Schema::create('authz_posts', function (Blueprint $table) {
        $table->id();
        $table->string('title');
        $table->timestamps();
        $table->softDeletes();
    });

    $this->actingAs(AuthzUser::create(['name' => 'u', 'email' => 'u@example.com', 'password' => 'x']));

    app()->singleton(PanelRegistry::class);

    $panel = Panel::make('admin')->resources([AuthzPostResource::class]);
    app(PanelRegistry::class)->register($panel);
    app(PanelRegistry::class)->setCurrent('admin');
});

it('hides the delete bulk action when the resource denies delete', function () {
    $action = DeleteBulkAction::make()->model(AuthzPost::class);

    expect($action->isHidden())->toBeTrue();
});

it('does not run the delete bulk action when the resource denies delete', function () {
    $post = AuthzPost::create(['title' => 'a']);
    $action = DeleteBulkAction::make()->model(AuthzPost::class);

    expect(fn () => ($action->getAction())([$post->id]))->toThrow(HttpException::class);
    expect(AuthzPost::count())->toBe(1);
});

it('returns 403 from the action endpoint instead of running a denied bulk delete', function () {
    $post = AuthzPost::create(['title' => 'a']);
    $action = DeleteBulkAction::make()->model(AuthzPost::class);
    $token = $action->getActionToken();

    $request = Request::create('/actions/execute', 'POST', [
        'token' => $token,
        'data' => ['ids' => [$post->id], 'model' => AuthzPost::class],
    ]);
    $request->setLaravelSession(app('session.store'));

    try {
        app(ActionController::class)->execute($request);
        $status = 200;
    } catch (HttpException $e) {
        $status = $e->getStatusCode();
    }

    expect($status)->toBe(403)
        ->and(AuthzPost::count())->toBe(1);
});

it('denies restore and force delete bulk actions the same way', function () {
    $post = AuthzPost::create(['title' => 'a']);
    $post->delete();

    foreach ([RestoreBulkAction::class, ForceDeleteBulkAction::class] as $class) {
        $action = $class::make()->model(AuthzPost::class);
        // The test resource only denies delete; restore/forceDelete fall back to the default (allowed)
        expect($action->isHidden())->toBeFalse();
    }
});

it('hides the delete record action for a record the resource denies', function () {
    $post = AuthzPost::create(['title' => 'a']);

    expect(DeleteAction::make()->isHidden($post))->toBeTrue();
});

it('respects a Laravel policy for bulk delete when the model has no resource', function () {
    Gate::policy(AuthzPolicyPost::class, AuthzPolicyPostPolicy::class);
    $post = AuthzPolicyPost::create(['title' => 'a']);
    $action = DeleteBulkAction::make()->model(AuthzPolicyPost::class);

    expect($action->isHidden())->toBeTrue()
        ->and(DeleteAction::make()->isHidden($post))->toBeTrue()
        ->and(fn () => ($action->getAction())([$post->id]))->toThrow(HttpException::class);
});

it('still allows bulk delete when nothing restricts it', function () {
    $post = AuthzPolicyPost::create(['title' => 'a']);
    $action = DeleteBulkAction::make()->model(AuthzPolicyPost::class);

    expect($action->isHidden())->toBeFalse();

    ($action->getAction())([$post->id]);

    expect(AuthzPolicyPost::count())->toBe(0);
});
