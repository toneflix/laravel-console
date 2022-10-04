<?php

namespace ToneflixCode\LaravelVisualConsole\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Madnest\Madzipper\Madzipper;
use Spatie\SlackAlerts\Facades\SlackAlert;

class Restore extends Command
{
    /**
     * Perform a system restoration from the last or one of the available backups
     *
     * @param  string|null  $signature
     * @param  bool  $delete
     * @return int
     */
    protected function now($signature, $delete = false): int
    {
        $this->info(Str::of(env('APP_URL'))->trim('/http://https://').' Is being restored.');
        SlackAlert::message(Str::of(env('APP_URL'))->trim('/http://https://').' Is being restored.');

        SlackAlert::message('System restore started at: '.Carbon::now());
        $this->info('System restore started.');

        // Delete public Symbolic links
        file_exists(public_path('media')) && unlink(public_path('media'));
        file_exists(public_path('storage')) && unlink(public_path('storage'));
        file_exists(public_path('avatars')) && unlink(public_path('avatars'));
        $this->info('Public Symbolic links deleted.');

        $backupDisk = Storage::disk('protected');
        $backupPath = $backupDisk->path('backup/');

        if ($signature) {
            $database = 'backup-'.$signature.'.sql';
            $package = 'backup-'.$signature.'.zip';
        } else {
            $database = collect($backupDisk->allFiles('backup'))->filter(fn ($f) => Str::contains($f, '.sql'))->map(fn ($f) => Str::replace('backup/', '', $f))->last();
            $package = collect($backupDisk->allFiles('backup'))->filter(fn ($f) => Str::contains($f, '.zip'))->map(fn ($f) => Str::replace('backup/', '', $f))->last();
        }

        $signature = $signature ?? collect($backupDisk->allFiles('backup'))->map(fn ($f) => Str::of($f)->substr(0, -4)->replace(['backup', '/-'], ''))->last();

        $this->info(Str::of(env('APP_URL'))->trim('/http://https://').' Is being restored.');
        SlackAlert::message(Str::of(env('APP_URL'))->trim('/http://https://').' Is being restored.');

        $canData = false;
        $canPack = false;
        if ($backupDisk->exists($path = 'backup/'.$database)) {
            $sql = $backupDisk->get($path);
            DB::unprepared($sql);
            $canData = true;
        }

        if ($backupDisk->exists($path = 'backup/'.$package)) {
            $zip = new Madzipper;
            $zip->make($backupPath.$package)->extractTo(storage_path(''));

            // Create public Symbolic links
            Artisan::call('storage:link');
            $this->info('Public Symbolic links created.');

            if ($delete) {
                unlink($backupPath.$database);
                unlink($backupPath.$package);
                $this->info("backup signature $signature deleted.");
            }
            $canPack = true;
        }

        if ($canPack || $canData) {
            $this->info("System has been restored to $signature backup signature.");

            return 0;
        }

        $this->error('System restore failed, no backup available.');

        return 1;
    }
}