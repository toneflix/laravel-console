<?php

namespace ToneflixCode\LaravelVisualConsole;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use ToneflixCode\LaravelVisualConsole\Commands\CommandConsole;

class LaravelVisualConsoleServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        Blade::componentNamespace("ToneflixCode\\LaravelVisualConsole\\View\\Components", 'visualconsole');
        config([ 'slack-alerts.webhook_urls' => config('laravel-visualconsole.slack_webhook_urls', env('SLACK_ALERT_WEBHOOK'))]);

        config([
            'filesystems.disks' => array_merge(
                config('filesystems.disks', []),
                ['google' => [
                    'driver' => config('laravel-visualconsole.backup_disk', 'google'),
                    'clientId' => env('GOOGLE_DRIVE_CLIENT_ID'),
                    'clientSecret' => env('GOOGLE_DRIVE_CLIENT_SECRET'),
                    'refreshToken' => env('GOOGLE_DRIVE_REFRESH_TOKEN'),
                    'folder' => env('GOOGLE_DRIVE_FOLDER'), // without folder is root of drive or team drive
                    'teamDriveId' => env('GOOGLE_DRIVE_TEAM_DRIVE_ID'),
                ]],
        )]);

        try {
            Storage::extend('google', function ($app, $config) {
                $options = [];

                if (! empty($config['teamDriveId'] ?? null)) {
                    $options['teamDriveId'] = $config['teamDriveId'];
                }

                $client = new \Google\Client();
                $client->setClientId($config['clientId']);
                $client->setClientSecret($config['clientSecret']);
                $client->refreshToken($config['refreshToken']);

                $service = new \Google\Service\Drive($client);
                $adapter = new \Masbug\Flysystem\GoogleDriveAdapter($service, $config['folder'] ?? '/', $options);
                $driver = new \League\Flysystem\Filesystem($adapter);

                return new \Illuminate\Filesystem\FilesystemAdapter($driver, $adapter);
            });
        } catch (\Exception $e) {
            // your exception handling logic
        }

        Collection::macro('paginator', function ($perPage = 15, $currentPage = null, $options = []) {
            $currentPage = $currentPage ?: (Paginator::resolveCurrentPage() ?: 1);

            return new LengthAwarePaginator(
                $this->forPage($currentPage, $perPage),
                $this->count(),
                $perPage,
                $currentPage,
                array_merge([
                    'path' => request()->fullUrl(),
                    'query' => [
                        'page' =>   request()->input('page', 1)
                    ]
                ], $options)
            );
        });

        /*
         * Optional methods to load your package assets
         */
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'laravel-visualconsole');
        $this->loadViewsFrom(__DIR__.'/views', 'laravel-visualconsole');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadRoutesFrom(__DIR__.'/routes/route.php');
        if (file_exists($routes = base_path('routes/visualconsole/routes.php'))) {
            $this->loadRoutesFrom($routes);
        }

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/config/config.php' => config_path('visualconsole.php'),
            ], 'visualconsole-config');

            // Publishing the views.
            $this->publishes([
                __DIR__.'/views' => resource_path('views/vendor/visualconsole'),
            ], 'visualconsole-views');

            // Publishing assets.
            $this->publishes([
                __DIR__.'/../assets' => public_path('visualconsole/assets'),
            ], 'visualconsole-assets');

            // Publishing routes.
            $this->publishes([
                __DIR__.'/routes' => base_path('routes/visualconsole'),
            ], 'visualconsole-routes');
        }

        // Registering package commands.
        // Console only commands
        $this->commands([
            CommandConsole::class,
        ]);
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/config/config.php', 'laravel-visualconsole');

        // Register the main class to use with the facade
        $this->app->singleton('laravel-visualconsole', function () {
            return new LaravelVisualConsole;
        });
    }
}