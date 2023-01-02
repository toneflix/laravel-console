<?php

namespace ToneflixCode\LaravelVisualConsole\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AutoDeploy extends Command
{
    protected $count = 0;
    protected $logLevel = 1;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system:git-deploy
                            {--mock-php : Mock the php binary from the config file}
                            {--branch= : The branch to deploy}
                            {--force : Force the deployment}
                            {--dev : Run in development mode (This will prevent composer from removing dev dependencies)}
                            {--log-level=2 : How log the output should handled. 0 = none, 1 = console only, 2 = file and console}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically deploys the latest code from the git repository associated with this project. Before you run this command, make sure you have set up a git repository and have added a remote named "origin".';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->logLevel = intval($this->option('log-level') ?? '1');
        if ($this->logLevel > 2) {
            $this->logLevel = 2;
        }

        $branch = $this->option('branch') ?? 'main';
        $output = null;
        $retval = null;
        $this->info('Starting deployment...');
        $this->info('Checking for uncommitted changes...');

        // Check if is git repository
        unset($output);
        $res = exec('git rev-parse --is-inside-work-tree', $output, $retval);
        if ($retval) {
            $this->writeOutputToFile(["git rev-parse --is-inside-work-tree", 'This is not a git repository.']);
            $this->error('This is not a git repository.');
            return Command::FAILURE;
        }

        // Check for uncommitted changes
        unset($output);
        $res = exec('git status --porcelain', $output, $retval);
        $this->writeOutputToFile(["git status --porcelain", ...$output], $res);

        if ($retval) {
            if (!$this->option('force')) {
                $this->error('There are uncommitted changes. Please commit or stash them before deploying.');
                return Command::FAILURE;
            }

            // Force deployment
            $this->warn('Forcing deployment...');

            return $this->deploy($branch);
        } else {
            $this->info('No uncommitted changes found.');
        }

        return $this->deploy($branch);
    }

    /**
     * Deploy the latest code
     *
     * @param string $branch
     * @return int
     */
    protected function deploy($branch)
    {
        $this->info("Checking out the \"{$branch}\" branch...");
        unset($output);
        $res = exec("git checkout {$branch}", $output, $retval);
        $this->writeOutputToFile(["git checkout", ...$output], $res);
        if ($retval) {
            $this->error('There was an error checking out the branch.');
            return Command::FAILURE;
        }

        // Pull the latest code
        $this->info('Pulling the latest code...');
        unset($output);
        $res = exec('git pull', $output, $retval);
        $this->writeOutputToFile(["git pull", ...$output], $res);
        if ($retval) {
            $this->error('There was an error pulling the latest code.');
            return Command::FAILURE;
        }

        // Run composer
        $this->info('Running composer...');
        $compose = '';
        if ($this->option('mock-php')) {
            $compose .= file_exists(config('laravel-visualconsole.php_bin'))
                ? config('laravel-visualconsole.php_bin')
                : PHP_BINARY;
            $compose .= ' ' . exec('which composer');
        } else {
            $compose .= 'composer';
        }

        $compose .= $this->option('dev') ? ' install' : ' install --no-dev';
        $compose .= ' --ignore-platform-reqs | sed -r "s/\x1B\[([0-9]{1,3}(;[0-9]{1,2};?)?)?[mGK]//g"';
        unset($output);

        $res = exec($compose, $output, $retval);

        $this->writeOutputToFile([$compose, ...$output], $res);
        if ($retval) {
            $this->error('There was an error running composer.');
            return Command::FAILURE;
        }

        // Run migrations
        $this->info('Running migrations...');
        unset($output);
        $php = file_exists(base_path(config('laravel-visualconsole.php_bin')))
            ? base_path(config('laravel-visualconsole.php_bin'))
            : PHP_BINARY;

        $res = exec($php . ' '. base_path('/artisan') . ' migrate', $output, $retval);
        $this->writeOutputToFile(["php artisan migrate", ...$output], $res);
        if ($retval) {
            $this->error('There was an error running migrations.');
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * Write the output to a file
     *
     * @param array $output
     * @return void
     */
    protected function writeOutputToFile($output, $result = null)
    {
        if ($this->logLevel === 0) {
            return;
        }

        $this->writeOutputToConsole($output, $result);

        $trace = [];
        $this->count = 0 * 1 - 1;
        foreach ($output as $key => $line) {
            $line = trim($line);
            if ($key === 0 || $line === '') {
                continue;
            }
            // Increment the count from 1
            $this->count++;
            // Append the output to the file
            $trace[] = "#{$this->count} {$line}" . PHP_EOL;
        }

        $stacktrace = implode('', $trace);

        Log::build([
            'driver' => 'daily',
            'path' => storage_path('logs/deploy.log'),
            'channels' => ['syslog'],
          ])->debug(($output[0] ?? 'Auto Deploy') . PHP_EOL . "[stacktrace]" . PHP_EOL . $stacktrace);
    }

    public function writeOutputToConsole($output, $result = null)
    {
        if ($this->logLevel === 2) {
            $trace = [];
            foreach ($output as $key => $line) {
                if ($key === 0 || $line === '' || $line === 'true') {
                    continue;
                }
                // Append the output to the file
                $trace[] = trim($line) . PHP_EOL;
            }

            $stacktrace = implode('', $trace);
            $this->info($stacktrace ?? 'Auto Deploy');
        }
    }
}
