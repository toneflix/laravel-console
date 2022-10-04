<?php

namespace ToneflixCode\LaravelVisualConsole;

use Illuminate\Support\ServiceProvider;
use ToneflixCode\LaravelVisualConsole\Commands\CommandConsole;

class LaravelVisualConsoleServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        /*
         * Optional methods to load your package assets
         */
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'laravel-visualconsole');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'laravel-visualconsole');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('laravel-visualconsole.php'),
            ], 'config');

            // Publishing the views.
            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/laravel-visualconsole'),
            ], 'views');

            // Publishing assets.
            /*$this->publishes([
                __DIR__.'/../resources/assets' => public_path('vendor/laravel-visualconsole'),
            ], 'assets');*/

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