<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use ToneflixCode\LaravelVisualConsole\Controllers\ManagementController;
use ToneflixCode\LaravelVisualConsole\LaravelVisualConsole;
use ToneflixCode\LaravelVisualConsole\Middleware\Authenticate;
use ToneflixCode\LaravelVisualConsole\Middleware\RedirectIfAuthenticated;

Route::prefix('console')->name('laravel-visualconsole.')->group(function () {
    Route::get('/login', [ManagementController::class, 'login'])
        ->middleware('web')
        ->name('login');

    Route::post('/login', [ManagementController::class, 'store'])
    ->middleware(RedirectIfAuthenticated::class);

    Route::post('/logout', [ManagementController::class, 'destroy'])
    ->middleware(['auth:lvc'])
    ->name('logout');

    Route::get('/user', [ManagementController::class, 'index'])
    ->middleware([Authenticate::class])
    ->name('user');
});

Route::get('images/dynamic/{file}', function ($file) {
    return (new LaravelVisualConsole)->privateFile($file);
})->name('in.private.file');

Route::get('/artisan/backup/action/{action?}', [ManagementController::class, 'backup'])->middleware(['web', 'auth', 'admin']);
Route::get('/artisan/{command}/{params?}', [ManagementController::class, 'artisan'])->middleware(['web', 'auth', 'admin']);

Route::get('downloads/secure/instant/{filename?}', function ($filename = '') {
    $disk = Storage::disk(config('laravel-visualconsole.backup_disk', 'local'));
    if ($disk->exists('backups/'.$filename)) {
        return $disk->download('backups/'.$filename);
    }

    return abort(404, 'File not found');
})
->middleware(['web', 'auth'])
->name('in.secure.download');