<?php

namespace ToneflixCode\LaravelVisualConsole;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use ToneflixCode\LaravelVisualConsole\Commands\CommandConsole;
use ToneflixCode\LaravelVisualConsole\Models\User;

class LaravelVisualConsoleServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        Auth::viaRequest('lvsusers-guard', function (Request $request) {
            dd('lvc in boots', $request);
            return User::where('token', $request->token)->first();
        });

        config([
            'auth.guards.lvc' => [
                'driver' => 'session',
                'provider' => 'lvsusers',
            ],
            'auth.providers.lvsusers' => [
                'driver' => 'eloquent',
                'model' => config('laravel-visualconsole.user_model', User::class),
            ],
        ]);

        Blade::componentNamespace("ToneflixCode\\LaravelVisualConsole\\View\\Components", 'visualconsole');
        config([ 'slack-alerts.webhook_urls' => config('laravel-visualconsole.slack_webhook_urls', env('SLACK_ALERT_WEBHOOK'))]);
        /*
         * Optional methods to load your package assets
         */
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'laravel-visualconsole');
        $this->loadViewsFrom(__DIR__.'/../views', 'laravel-visualconsole');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadRoutesFrom(__DIR__.'/routes.php');
        if (file_exists($routes = base_path('routes/laravel-visualconsole/routes.php'))) {
            $this->loadRoutesFrom($routes);
        }

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('laravel-visualconsole.php'),
            ], 'config');

            // Publishing the views.
            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/laravel-visualconsole'),
            ], 'views');

            // Publishing assets.
            $this->publishes([
                __DIR__.'/routes' => app_path('routes/laravel-visualconsole'),
            ], 'assets');

            // Publishing the translation files.
            /*$this->publishes([
                __DIR__.'/../resources/lang' => resource_path('lang/vendor/laravel-visualconsole'),
            ], 'lang');*/

            // Registering package commands.
            // Console only commands
            $this->commands([
                CommandConsole::class,
            ]);
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'laravel-visualconsole');

        // Register the main class to use with the facade
        $this->app->singleton('laravel-visualconsole', function () {
            return new LaravelVisualConsole;
        });
    }
}
