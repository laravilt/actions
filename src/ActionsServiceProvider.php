<?php

namespace Laravilt\Actions;

use Illuminate\Support\ServiceProvider;

class ActionsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Merge config
        $this->mergeConfigFrom(
            __DIR__ . '/../config/laravilt-actions.php',
            'laravilt-actions'
        );

        // Register any services, bindings, or singletons here
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {

        // Load translations
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'actions');


        // Load web routes
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');


        if ($this->app->runningInConsole()) {
            // Publish config
            $this->publishes([
                __DIR__ . '/../config/laravilt-actions.php' => config_path('laravilt-actions.php'),
            ], 'laravilt-actions-config');

            // Publish assets
            $this->publishes([
                __DIR__ . '/../dist' => public_path('vendor/laravilt/actions'),
            ], 'laravilt-actions-assets');


            // Register commands
            $this->commands([
                Commands\InstallActionsCommand::class,
                Commands\MakeActionCommand::class,
            ]);
        }
    }
}
