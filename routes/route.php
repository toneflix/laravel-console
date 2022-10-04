<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use ToneflixCode\LaravelVisualConsole\Controllers\ManagementController;

Route::prefix('console')->name('console.')->group(function () {
    Route::get('/login', [ManagementController::class, 'login'])
        ->middleware('guest')
        ->name('login');

    Route::post('/login', [ManagementController::class, 'store'])
    ->middleware('guest');

    Route::post('/logout', [ManagementController::class, 'destroy'])
    ->middleware(['web', 'auth'])
    ->name('logout');

    Route::get('/user', [ManagementController::class, 'index'])
    ->middleware(['web', 'auth', 'admin'])
    ->name('user');
});

Route::get('/artisan/backup/action/{action?}', [ManagementController::class, 'backup'])->middleware(['web', 'auth', 'admin']);
Route::get('/artisan/{command}/{params?}', [ManagementController::class, 'artisan'])->middleware(['web', 'auth', 'admin']);

Route::get('downloads/secure/{filename?}', function ($filename = '') {
    if (Storage::disk('protected')->exists('backup/'.$filename)) {
        return Storage::disk('protected')->download('backup/'.$filename);
    }

    return abort(404, 'File not found');
})
->middleware(['web', 'auth', 'admin'])
->name('secure.download');