<?php

namespace ToneflixCode\LaravelVisualConsole\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Madnest\Madzipper\Madzipper;
use Spatie\SlackAlerts\Facades\SlackAlert;

trait Backup
{
    /**
     * Perform a backup of the system's database and uploaded media content
     *
     * @return int
     */
    public function backup(): int
    {
        $this->info(Str::of(env('APP_URL'))->trim('/http://https://').' Is being backed up.');
        SlackAlert::message(Str::of(env('APP_URL'))->trim('/http://https://').' Is being backed up.');

        SlackAlert::message('System backup started at: '.Carbon::now());
        $this->info('System backup started.');

        $backupDisk = Storage::disk(config('laravel-visualconsole.backup_disk', 'local'));

        // Create the backup directory if it does not exist
        if (! $backupDisk->exists('backups')) {
            $backupDisk->makeDirectory('backups');
        }
        $backupPath = $backupDisk->path('backups/');

        // Backup the database
        $filename = 'backup-'.\Carbon\Carbon::now()->format('Y-m-d_H-i-s');
        $db_backup_path = "{$backupPath}$filename.sql";

        if (! File::exists(storage_path("tempBkpDir1994"))) {
            File::makeDirectory(storage_path("tempBkpDir1994"));
        }
        $command = 'mysqldump --skip-comments'
        .' --user='.env('DB_USERNAME')
        .' --password='.env('DB_PASSWORD')
        .' --host='.env('DB_HOST')
        .' '.env('DB_DATABASE').' > '. storage_path("tempBkpDir1994/$filename.sql");
        $returnVar = null;
        $output = null;
        exec($command, $output, $returnVar);
        $backupDisk->put($db_backup_path, File::get(storage_path("tempBkpDir1994/$filename.sql")));

        // Backup the files.
        $zip_backup_path = "{$backupPath}$filename.zip";
        $zip = new Madzipper;

        $zipping = $zip->make(storage_path("tempBkpDir1994/$filename.zip"))
            ->folder('app')
            ->add("storage/app")->close();

        $signature = Str::of($filename)->substr(0, -4)->replace(['backup-', '/'], '');
        if (! File::exists(storage_path("tempBkpDir1994"))) {
            File::makeDirectory(storage_path("tempBkpDir1994"));
        }
        $backupDisk->put($zip_backup_path, File::get(storage_path("tempBkpDir1994/$filename.zip")));
        // File::deleteDirectory(storage_path("tempBkpDir1994"));

        // Generate Link
        if (app()->runningInConsole()) {
            $link = app()->runningInConsole() ? $this->choice('Should we generate a link to download your backup files?', ['No', 'Yes'], 1, 2) : 'Yes';
            if ($link === 'Yes' && $backupDisk->exists("backups/$filename.sql")) {

                // Generate a downloadable link for this backup
                $zip = new Madzipper;
                $zip->make(storage_path("tempBkpDir1994/pack-$filename.zip"))->folder('backups')
                    ->add([storage_path("tempBkpDir1994/$filename.sql"), storage_path("tempBkpDir1994/$filename.zip")]);

                $link_url = route('in.secure.download', $filename.'.zip');

                $mail = app()->runningInConsole() ? $this->choice('Should we mail you the link?', ['No, I\'ll copy from here.', 'Yes, mail me'], 1, 2) : 'No';

                if ($mail === 'Yes, mail me' && $zip->getFilePath()) {
                    $address = $this->ask('Email Address:');
                    Mail::send('laravel-visualconsole::email', [
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
            } elseif (! $backupDisk->exists("backups/$filename.sql")) {
                $this->error('Failed to send link.');
            }
        } else {
            // Generate a downloadable link for this backup
            $zip = new Madzipper;
            $zip->make("{$backupPath}$filename.zip")->folder('backups')
                ->add([$backupPath.$filename.'.sql', $backupPath.$filename.'.zip']);

            $link_url = route('in.secure.download', $filename.'.zip');
            $this->info("Download your backup file through this link: $link_url.");
        }

        SlackAlert::message('System backup completed at: '.Carbon::now());
        $this->info("System backup completed successfully (Signature: $signature).");

        return 0;
    }
}
