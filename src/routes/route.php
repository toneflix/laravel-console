<?php

use ToneflixCode\LaravelVisualConsole\Middleware\Authenticate;
use ToneflixCode\LaravelVisualConsole\Middleware\RedirectIfAuthenticated;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use ToneflixCode\LaravelVisualConsole\Controllers\LogViewerController;
use ToneflixCode\LaravelVisualConsole\Controllers\ManagementController;
use ToneflixCode\LaravelVisualConsole\LaravelVisualConsole;
use ToneflixCode\LaravelVisualConsole\Middleware\IsAdmin;

Route::prefix(config('laravel-visualconsole.route_prefix', 'system'))
     ->name(config('laravel-visualconsole.route_prefix', 'system') . '.')->group(function () {
    Route::prefix('console')->name('console.')->group(function () {
        Route::get('/login', [ManagementController::class, 'login'])
            ->middleware(['web', RedirectIfAuthenticated::class])
            ->name('login');

        Route::post('/login', [ManagementController::class, 'store'])
            ->middleware(['web', RedirectIfAuthenticated::class]);

        Route::post('/logout', [ManagementController::class, 'destroy'])
            ->middleware(['web', Authenticate::class])
            ->name('logout');

        Route::get('/user', [ManagementController::class, 'index'])
            ->middleware(['web', Authenticate::class, IsAdmin::class])
            ->name('user');

        Route::get('/jobs/{type?}', [ManagementController::class, 'jobs'])
            ->middleware(['web', Authenticate::class, IsAdmin::class])
            ->name('jobs');

        Route::get('error/logs', [LogViewerController::class, 'index'])
            ->middleware(['web', Authenticate::class, IsAdmin::class])
            ->name('error.logs');

        Route::match(['post', 'get'], 'controls/{type?}', [ManagementController::class, 'backupUtility'])
            ->middleware(['web', Authenticate::class, IsAdmin::class])
            ->name('controls');
    });

    Route::get('/artisan/backup/action/{action?}', [ManagementController::class, 'backup'])
         ->middleware(['web', Authenticate::class, IsAdmin::class])->name('backup');

    Route::get('/artisan/{command}/{params?}', [ManagementController::class, 'artisan'])
         ->middleware(['web', Authenticate::class, IsAdmin::class])->name('artisan');

    Route::get('downloads/secure/{filename?}', function ($filename = '') {
        if (Storage::disk('protected')->exists('backup/'.$filename)) {
            return Storage::disk('protected')->download('backup/'.$filename);
        }

        return abort(404, 'File not found');
    })
    ->middleware(['web', Authenticate::class, IsAdmin::class])
    ->name('secure.download');

    Route::get('images/dynamic/{file}', function ($file) {
        return (new LaravelVisualConsole)->privateFile($file);
    })->name('in.private.file');
});

Route::get('visualconsole/assets/{file}', function ($file) {
    return (new LaravelVisualConsole)->assetFile($file);
})->name('system.in.asset.file');