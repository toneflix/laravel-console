<?php

namespace ToneflixCode\LaravelVisualConsole\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Madnest\Madzipper\Madzipper;
use Spatie\SlackAlerts\Facades\SlackAlert;

class Restore extends Command
{
    /**
     * Perform a backup of the system's database and uploaded media content
     *
     * @return int
     */
    protected function now(): int
    {
        $this->info(Str::of(env('APP_URL'))->trim('/http://https://').' Is being backed up.');
        SlackAlert::message(Str::of(env('APP_URL'))->trim('/http://https://').' Is being backed up.');

        SlackAlert::message('System backup started at: '.Carbon::now());
        $this->info('System backup started.');

        $backupDisk = Storage::disk('protected');

        // Create the backup directory if it does not exist
        if (! $backupDisk->exists('backup')) {
            $backupDisk->makeDirectory('backup');
        }
        $backupPath = $backupDisk->path('backup/');

        // Backup the database
        $filename = 'backup-'.\Carbon\Carbon::now()->format('Y-m-d_H-i-s');
        $db_backup_path = "{$backupPath}{$filename}.sql";

        $command = 'mysqldump --skip-comments'
        .' --user='.env('DB_USERNAME')
        .' --password='.env('DB_PASSWORD')
        .' --host='.env('DB_HOST')
        .' '.env('DB_DATABASE').' > '.$db_backup_path;
        $returnVar = null;
        $output = null;
        exec($command, $output, $returnVar);

        // Backup the files.
        $zip_backup_path = $backupDisk->put("backup/$filename.zip", '');
        $zip_backup_path = "{$backupPath}{$filename}.zip";
        $zip = new Madzipper;
        $zip->make($zip_backup_path)->folder('app')->add([storage_path('app')]);
        $signature = Str::of($filename)->substr(0, -4)->replace(['backup-', '/'], '');

        // Generate Link
        if (app()->runningInConsole()) {
            $link = app()->runningInConsole() ? $this->choice('Should we generate a link to download your backup files?', ['No', 'Yes'], 1, 2) : 'Yes';
            if ($link === 'Yes' && $backupDisk->exists("backup/{$filename}.sql")) {

                // Generate a downloadable link for this backup
                $zip = new Madzipper;
                $zip->make($zip_backup_path)->folder('backup')
                    ->add([$db_backup_path, $zip_backup_path]);

                $link_url = route('secure.download', $filename.'.zip');

                $mail = app()->runningInConsole() ? $this->choice('Should we mail you the link?', ['No, I\'ll copy from here.', 'Yes, mail me'], 1, 2) : 'No';

                if ($mail === 'Yes, mail me' && $zip->getFilePath()) {
                    $address = $this->ask('Email Address:');
                    Mail::send('email', [
                        'name' => ($name = collect(explode('@', $address)))->last(),
                        'message_line1' => 'You requested that we mail you a link to download your system backup.',
                        'cta' => ['link' => $link_url, 'title' => 'Download'],
                    ], function ($message) use ($address, $name) {
                        $message->to($address, $name)->subject('Backup');
                        //    $message->attach(storage_path("app/secure/$filename.zip"));
                        $message->from(env('MAIL_FROM_ADDRESS'), config('settings.site_name'));
                    });

                    SlackAlert::message("We have sent the download link to your backup file to $address.");
                    $this->info("We have sent the download link to your backup file to $address.");
                } elseif (! $zip->getFilePath()) {
                    SlackAlert::message('You have requested that we we mail you the link to your backup file but we failed to fetch the link.');
                    $this->error('Failed to fetch link.');
                } else {
                    SlackAlert::message("Download your backup file through this link: $link_url.");
                    $this->info("Download your backup file through this link: $link_url.");
                }
            } elseif (! $backupDisk->exists("backup/{$filename}.sql")) {
                $this->error('Failed to send link.');
            }
        } else {
            // Generate a downloadable link for this backup
            $zip = new Madzipper;
            $zip->make(storage_path("app/secure/$filename.zip"))->folder('backup')
                ->add([$backupPath.$filename.'.sql', $backupPath.$filename.'.zip']);

            $link_url = route('secure.download', $filename.'.zip');
            $this->info("Download your backup file through this link: $link_url.");
        }

        SlackAlert::message('System backup completed at: '.Carbon::now());
        $this->info("System backup completed successfully (Signature: $signature).");

        return 0;
    }
}