<?php

namespace ToneflixCode\LaravelConsole\Controllers;

use ToneflixCode\LaravelConsole\HttpStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;
use Symfony\Component\Console\Exception\CommandNotFoundException;
use Symfony\Component\Console\Exception\RuntimeException;

class ManagementController extends Controller
{
    protected $commands = [
        ['warn' => false, 'command' => 'artisan/list', 'label' => 'Help and Info'],
        ['warn' => false, 'command' => 'artisan/storage:link', 'label' => 'Sym Link Storage'],
        ['warn' => false, 'command' => 'artisan/queue:work', 'label' => 'Run Queues'],
        ['warn' => false, 'command' => 'artisan/migrate', 'label' => 'Migrate Database'],
        ['warn' => true, 'command' => 'artisan/db:seed', 'label' => 'Seed Database'],
        ['warn' => false, 'command' => 'artisan/db:seed HomeDataSeeder', 'label' => 'Seed Homepage'],
        ['warn' => false, 'command' => 'artisan/backup/action/download', 'label' => 'Download Backups'],
        ['warn' => false, 'command' => 'artisan/backup/action/choose', 'label' => 'System Restore (Choose Backup)'],
        ['warn' => false, 'command' => 'artisan/config:cache', 'label' => 'Cache Config'],
        ['warn' => false, 'command' => 'artisan/optimize:clear', 'label' => 'Clear Cache'],
        ['warn' => false, 'command' => 'artisan/route:list', 'label' => 'Route List'],
        ['warn' => true, 'command' => 'artisan/migrate:rollback', 'label' => 'Rollback Last Database Migration'],
        ['warn' => true, 'command' => 'artisan/migrate:fresh --seed', 'label' => 'Refresh Database'],
        ['warn' => true, 'command' => 'artisan/system:reset backup', 'label' => 'System Backup'],
        ['warn' => false, 'command' => 'artisan/system:reset -h', 'label' => 'System Reset Help'],
        ['warn' => true, 'command' => 'artisan/system:reset -b', 'label' => 'System Reset (Backup)'],
        ['warn' => true, 'command' => 'artisan/system:reset', 'label' => 'System Reset (No Backup)'],
        ['warn' => true, 'command' => 'artisan/system:reset -r', 'label' => 'System Reset (Restore Latest Backup)'],
        ['warn' => true, 'command' => 'artisan/system:reset restore', 'label' => 'System Restore (Latest Backup)'],
        ['warn' => false, 'command' => 'artisan/system:automate', 'label' => 'Run Automation'],
    ];

    public function index()
    {
        $user = Auth::user();
        $code = session()->get('code');
        $errors = session()->get('errors');
        $action = session()->get('action');
        $messages = session()->get('messages');
        $commands = $this->commands;

        return view('web-user', compact('user', 'errors', 'code', 'action', 'messages', 'commands'));
    }

    public function login()
    {
        return view('login');
    }

    /**
     * Handle an incoming authentication request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Response $response)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);
        
        Auth::attemptWhen($credentials);
        if (Auth::attemptWhen($credentials, function ($user) {
            return $user[config('laravel-console.permission_field')] == config('laravel-console.permission_value');
        })) {
            return redirect()->route('console.user');
        }

        return back()->withErrors([
            'email' => __('Invalid credentials.'),
        ])->withInput();
    }

    public function backup($action = 'choose')
    {
        $user = Auth::user();
        $code = session()->get('code');
        $errors = session()->get('errors');
        $action = session()->get('action', $action);
        $commands = $this->commands;

        if ($code) {
            return redirect()->route('console.user')->with(compact('errors', 'code', 'action'))->withInput();
        }

        return view('web-user', compact('user', 'errors', 'code', 'action', 'commands'));
    }

    public function artisan(Response $response, $command, $params = null)
    {
        $errors = $code = $messages = $action = null;
        try {
            if ($params) {
                Artisan::call($command, $params ? explode(',', $params) : []);
            }
            Artisan::call(implode(' ', explode(',', $command)), []);
            $code = collect(nl2br(Artisan::output()));
        } catch (CommandNotFoundException | InvalidArgumentException | RuntimeException $e) {
            $errors = collect([$e->getMessage()]);
        }

        return back()->with(compact('errors', 'code', 'action'))->withInput();
    }

    /**
     * Destroy an authenticated session.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $request->user()->tokens()->delete();

        if (! $request->isXmlHttpRequest()) {
            session()->flush();

            return response()->redirectToRoute('console.login');
        }

        return $this->buildResponse([
            'message' => __('You are now logged out'),
            'status' => 'success',
            'status_code' => HttpStatus::OK,
        ]);
    }
}