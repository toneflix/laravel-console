<?php

namespace ToneflixCode\LaravelVisualConsole\Commands;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Spatie\SlackAlerts\Facades\SlackAlert;

trait Reset
{
    /**
     * Reset the system to default
     *
     * @param  bool  $backup
     * @return int
     */
    public function reset(bool $backup = false)
    {
        $this->info(Str::of(env('APP_URL'))->trim('/http://https://').' Is being reset.');
        if (collect(config('laravel-visualconsole.slack_webhook_urls'))->first()) {
            SlackAlert::message(Str::of(env('APP_URL'))->trim('/http://https://').' Is being reset.');
        }

        // Backup the system
        if ($backup) {
            $this->backup();
        }

        // SlackAlert::message('System reset started at: '.Carbon::now());
        $this->info('System reset started.');

        $publicFiles = collect(config('filesystems.links'))->merge(config('laravel-visualconsole.paths', []));

        // Delete Symbolic links
        $this->info('Deleting Public Symbolic links.');
        $publicFiles->each(function ($path, $link) {
            if (file_exists($link)) {
                $this->info('Deleting '.$link.'...');
                if (File::delete($link)) {
                    $this->info('Deleted '.$link);
                }
            }
        });
        $this->info('Public Symbolic links deleted.');

        // Delete directories
        $this->info('Deleting Public directories.');
        $publicFiles->each(function ($path, $link) {
            if (file_exists($path)) {
                $this->info('Deleting '.$path.'...');
                if (File::deleteDirectory($path)) {
                    $this->info('Deleted '.$path);
                }
            }
        });
        $this->info('Public directories deleted.');

        // Recreate Directories
        $this->info('Recreating Public directories.');
        $publicFiles->each(function ($path, $link) {
            if (!file_exists($path)) {
                $this->info('Creating '.$path.'...');
                if (File::makeDirectory($path)) {
                    $this->info($path.' Created.');
                }
            }
        });
        $this->info('Public directories created.');

        // Create public Symbolic links
        $this->info('Rereating Public Symbolic links.');
        Artisan::call('storage:link');
        $this->info('Public Symbolic links created.');

        if (Artisan::call('migrate:fresh') === 0) {
            if (Artisan::call('db:seed --class='.config('laravel-visualconsole.base_seeder').' --force') === 0) {
                if (collect(config('laravel-visualconsole.slack_webhook_urls'))->first()) {
                    SlackAlert::message('System reset completed at: '.Carbon::now());
                }
                $this->info('System reset completed successfully.');

                return 0;
            }
        }

        if (collect(config('laravel-visualconsole.slack_webhook_urls'))->first()) {
            SlackAlert::message('An error occured at: '.Carbon::now().'. Unable to complete system reset.');
        }
        $this->error('An error occured.');

        return 1;
    }
}