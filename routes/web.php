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

Route::middleware(['web'])->group(function () {
    Route::post('/actions/execute', [ActionController::class, 'execute'])->name('actions.execute');
});
