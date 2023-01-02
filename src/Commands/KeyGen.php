<?php

namespace ToneflixCode\LaravelVisualConsole\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use msztorc\LaravelEnv\Env;

class KeyGen extends Command
{
    protected $count = 0;
    protected $logLevel = 1;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system:key-gen';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a webhook secret key for the application.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $rand = random_bytes(32); // chiper = AES-256-CBC ? 32 : 16
        $key = 'base64:' . base64_encode($rand);
        (new Env)->setValue('VISUALCONSOLE_WEBHOOK_SECRET', $key);
        $this->info('Key generated successfully.');
        $this->comment($key);
    }
}
