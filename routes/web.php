<?php

use Illuminate\Support\Facades\Route;
use Laravilt\Actions\Http\Controllers\ActionController;

/*
|--------------------------------------------------------------------------
| Actions Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your plugin. These
| routes are loaded by the ServiceProvider within a group which
| contains the "web" middleware group.
|
*/

// Build middleware array - only include HandleLocalization if panel package is available
$middleware = ['web'];
if (class_exists(\Laravilt\Panel\Http\Middleware\HandleLocalization::class)) {
    $middleware[] = \Laravilt\Panel\Http\Middleware\HandleLocalization::class;
}

Route::middleware($middleware)->group(function () {
    Route::post('/actions/execute', [ActionController::class, 'execute'])->name('actions.execute');
    Route::get('/actions/export', [ActionController::class, 'export'])->name('actions.export');
    Route::post('/actions/import', [ActionController::class, 'import'])->name('actions.import');
});
