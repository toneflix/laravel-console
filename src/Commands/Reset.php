<?php

namespace ToneflixCode\LaravelVisualConsole\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\SlackAlerts\Facades\SlackAlert;

class Reset extends Command
{
    /**
     * Reset the system to default
     *
     * @param  bool  $backup
     * @return int
     */
    public function now(bool $backup = false)
    {
        $this->info(Str::of(env('APP_URL'))->trim('/http://https://').' Is being reset.');
        SlackAlert::message(Str::of(env('APP_URL'))->trim('/http://https://').' Is being reset.');

        // Backup the system
        if ($backup) {
            $this->backup();
        }

        SlackAlert::message('System reset started at: '.Carbon::now());
        $this->info('System reset started.');

        // Delete public Symbolic links
        file_exists(public_path('media')) && unlink(public_path('media'));
        file_exists(public_path('storage')) && unlink(public_path('storage'));
        file_exists(public_path('avatars')) && unlink(public_path('avatars'));
        $this->info('Public Symbolic links deleted.');

        // Delete directories
        Storage::deleteDirectory('public/avatars');
        Storage::deleteDirectory('public/media');
        Storage::deleteDirectory('files/images');
        $this->info('Public directories deleted.');

        // Recreate Directories
        Storage::makeDirectory('public/avatars');
        Storage::makeDirectory('public/media');
        Storage::makeDirectory('files/images');
        $this->info('Public directories created.');

        // Create public Symbolic links
        Artisan::call('storage:link');
        $this->info('Public Symbolic links created.');

        if (Artisan::call('migrate:refresh') === 0) {
            if (Artisan::call('db:seed') === 0 && Artisan::call('db:seed HomeDataSeeder') === 0) {
                SlackAlert::message('System reset completed at: '.Carbon::now());
                $this->info('System reset completed successfully.');

                return 0;
            }
        }
        SlackAlert::message('An error occured at: '.Carbon::now().'. Unable to complete system reset.');
        $this->error('An error occured.');

        return 1;
    }
}