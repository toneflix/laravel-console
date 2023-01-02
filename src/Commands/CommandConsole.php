<?php

namespace ToneflixCode\LaravelVisualConsole\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CommandConsole extends Command
{
    use Backup, Reset, Restore;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system:control
                            {action? : Action to perform [reset, backup, restore]}
                            {--w|wizard : Let the wizard guide you through the whole process.}
                            {--r|restore : Restore the system to the last backup or provide the --signature option to restore a known backup signature.}
                            {--s|signature= : Set the backup signature value to restore a particular known backup. E.g. 2022-04-26_16-05-34.}
                            {--b|backup : Do a complete system backup before the reset.}
                            {--d|delete : If the restore option is set, this option will delete the backup files after successfull restore.}
                            {--f|force : Force the action to execute.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Helps you perforom common system tasks like backup, backup restore and system reset.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $action = $this->argument('action');
        $backup = $this->option('backup');
        $restore = $this->option('restore');
        $signature = $this->option('signature');
        $delete = $this->option('delete');
        $wizard = $this->option('wizard');
        $backupDisk = Storage::disk('protected');

        if ($action === 'backup') {
            return $this->backup();
        } elseif ($action === 'restore') {
            $signatures = collect($backupDisk->allFiles('backup'))
                ->filter(fn ($f) => Str::contains($f, '.sql'))
                ->map(fn ($f) => Str::of($f)->substr(0, -4)->replace(['backup', '/-'], ''))->sortDesc()->values()->all();
            $signature = $this->choice('Backup Signature (Latest shown first):', $signatures, 0, 3);
            $delete = $this->choice('Delete Signature after restoration?', ['No', 'Yes'], 1, 2);

            return $this->restore($signature, $delete === 'Yes');
        }

        if ($wizard) {
            if (! app()->runningInConsole()) {
                $this->error('This action can only be run in a CLI.');

                return 0;
            }
            $action = $this->choice('What do you want to do?', ['backup', 'restore', 'reset'], 2, 3);

            if ($action === 'backup') {
                return $this->backup();
            } elseif ($action === 'restore') {
                $signatures = collect($backupDisk->allFiles('backup'))
                    ->filter(fn ($f) => Str::contains($f, '.sql'))
                    ->map(fn ($f) => Str::of($f)->substr(0, -4)->replace(['backup', '/-'], ''))->sortDesc()->values()->all();
                $signature = $this->choice('Backup Signature (Latest shown first):', $signatures, 0, 3);
                $delete = $this->choice('Delete Signature after restoration?', ['No', 'Yes'], 1, 2);

                return $this->restore($signature, $delete === 'Yes');
            } elseif ($action === 'reset') {
                $backup = $this->choice('Would you want do a sytem backup before reset?', ['No', 'Yes'], 1, 2);
                // Reset the system
                return $this->reset($backup === 'Yes');
            }

            return 0;
        } else {

            if (!$action) {
                $this->error('Please provide an action to perform [reset, backup, restore].');
                return 1;
            }

            // Restore the system backup
            if ($restore) {
                return $this->restore($signature, $delete);
            }

            if (!app()->runningInConsole() && !$this->option('force')) {
                $this->error('Please Run this action from console or use the [--force] flag.');
                return 1;
            }
            // Reset the system

            if (!$this->option('force')) {
                if (($i = $this->ask("Please enter \"".env('APP_NAME')."\" to continue")) !== env('APP_NAME')) {
                    $this->error("\"$i\" is not expected, action aborted!");
                    return 1;
                }
            }
            return $this->reset($backup);
        }
    }
}
