<?php

namespace ToneflixCode\LaravelVisualConsole\Controllers;

use App\Http\Controllers\Controller;
use ToneflixCode\LaravelVisualConsole\HttpStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use InvalidArgumentException;
use Symfony\Component\Console\Exception\CommandNotFoundException;
use Symfony\Component\Console\Exception\RuntimeException;
use ToneflixCode\LaravelVisualConsole\LaravelVisualConsole;

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
        ['warn' => true, 'command' => 'artisan/system:control backup', 'label' => 'System Backup'],
        ['warn' => false, 'command' => 'artisan/system:control -h', 'label' => 'System Control Help'],
        ['warn' => null, 'command' => 'artisan/system:control reset -b', 'label' => 'System Reset (Backup)'],
        ['warn' => null, 'command' => 'artisan/system:control reset', 'label' => 'System Reset (No Backup)'],
        ['warn' => null, 'command' => 'artisan/system:control reset -r', 'label' => 'System Reset (Restore Last Backup)'],
        ['warn' => null, 'command' => 'artisan/system:control restore', 'label' => 'System Restore (Last Backup)'],
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
        $total_jobs = DB::table('jobs')->count();
        $failed_jobs = DB::table('failed_jobs')->count();
        $tables_count = $this->getTables()->count();

        return view('laravel-visualconsole::web-user', compact(
            'user',
            'errors',
            'code',
            'action',
            'messages',
            'commands',
            'total_jobs',
            'failed_jobs',
            'tables_count'
        ));
    }

    public function login()
    {
        return view('laravel-visualconsole::login');
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

        if (Auth::attemptWhen($credentials, function ($user) {
            if (is_array($roles = $user[config('laravel-visualconsole.permission_field')])) {
                return in_array(config('laravel-visualconsole.permission_value'), $roles);
            } elseif (!config('laravel-visualconsole.permission_value') || !config('laravel-visualconsole.permission_field')) {
                return true;
            }

            return $user[config('laravel-visualconsole.permission_field')] == config('laravel-visualconsole.permission_value');
        })) {
            return redirect()->route(config('laravel-visualconsole.route_prefix', 'system') . '.console.user');
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
        $total_jobs = DB::table('jobs')->count();
        $failed_jobs = DB::table('failed_jobs')->count();
        $tables_count = $this->getTables()->count();

        if ($code) {
            return redirect()
                ->route(config('laravel-visualconsole.route_prefix', 'system') . '.console.user')
                ->with(compact('errors', 'code', 'action'))
                ->withInput();
        }


        return view('laravel-visualconsole::web-user', compact(
            'user',
            'errors',
            'code',
            'action',
            'commands',
            'total_jobs',
            'failed_jobs',
            'tables_count'
        ));
    }

    public function backupUtility(Request $request, $action = 'choose')
    {
        $user = Auth::user();
        $errors = session()->get('errors');
        $action = session()->get('action', $action);
        $messages = session()->get('messages');

        $backupDisk = \Storage::disk('protected');
        $backups = collect($backupDisk->allFiles('backup'))
            ->filter(fn ($f) => str($f)->contains('.sql') ||  str($f)->contains('.zip'))
            ->map(fn ($f) => str($f)->replace('backup/', '')->toString());

        //

        if ($request->isMethod('post')) {
            // Required all fields
            $request->validate([
                'GOOGLE_DRIVE_CLIENT_ID' => ['string', 'required'],
                'GOOGLE_DRIVE_CLIENT_SECRET' => ['string', 'required'],
                'GOOGLE_DRIVE_REFRESH_TOKEN' => ['string', 'required'],
                'GOOGLE_DRIVE_FOLDER' => ['string', 'nullable'],
                'GOOGLE_DRIVE_TEAM_DRIVE_ID' => ['string', 'nullable']
            ]);

            // Save to .env
            (new LaravelVisualConsole)->update_env([
                'GOOGLE_DRIVE_CLIENT_ID' => $request->input('GOOGLE_DRIVE_CLIENT_ID'),
                'GOOGLE_DRIVE_CLIENT_SECRET' => $request->input('GOOGLE_DRIVE_CLIENT_SECRET'),
                'GOOGLE_DRIVE_REFRESH_TOKEN' => $request->input('GOOGLE_DRIVE_REFRESH_TOKEN'),
                'GOOGLE_DRIVE_FOLDER' => $request->input('GOOGLE_DRIVE_FOLDER'),
                'GOOGLE_DRIVE_TEAM_DRIVE_ID' => $request->input('GOOGLE_DRIVE_TEAM_DRIVE_ID'),
            ]);

            // Redirect to backup and show success message
            return redirect()
                ->route(config('laravel-visualconsole.route_prefix', 'system') . '.console.controls')
                ->with('messages', collect(['Backup utility settings saved successfully.']));
        }

        return view('laravel-visualconsole::backup', compact(
            'user',
            'errors',
            'action',
            'backups',
            'messages',
        ));
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

        if (!$request->isXmlHttpRequest()) {
            session()->flush();

            return response()->redirectToRoute(config('laravel-visualconsole.route_prefix', 'system') . '.console.login');
        }

        return $this->buildResponse([
            'message' => __('You are now logged out'),
            'status' => 'success',
            'status_code' => HttpStatus::OK,
        ]);
    }

    public function jobs($type = null)
    {
        // Get all jobs from the queue
        $jobs = DB::table($type == 'failed' ? 'failed_jobs' : 'jobs')->paginate(30);
        $paginated = $jobs->getCollection()->map(function ($job) use ($type) {
            if ($type == 'failed') {
                $job->payload = json_decode($job->payload);
                $exception = explode('Stack trace:', $job->exception);
                $exception = ['title' => trim($exception[0]), 'data' => $exception[1] ?? []];
                $exception['data'] = collect(array_map(function ($line) {
                    return trim($line);
                }, explode('#', $exception['data'])))->filter()->map(function ($line) {
                    return explode(' ', $line, 2);
                })->map(function ($line) {
                    return $line[1];
                })->toArray();
                $job->exception = $exception;
                return $job;
            } else {
                $job->payload = json_decode($job->payload);
                // $job->payload->data->command = unserialize($job->payload->data->command);

                return $job;
            }
        })->toArray();

        $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $paginated,
            $jobs->total(),
            $jobs->perPage(),
            $jobs->currentPage(),
            [
                'path' => \Request::url(),
                'query' => [
                    'page' => $jobs->currentPage()
                ]
            ]
        );

        $user = Auth::user();
        $jobs = $paginated;
        $total_jobs = DB::table('jobs')->count();
        $failed_jobs = DB::table('failed_jobs')->count();
        $tables_count = $this->getTables()->count();
        $code = session()->get('code');
        $errors = session()->get('errors');
        $action = session()->get('action');
        $messages = session()->get('messages');
        $commands = $this->commands;

        return view('laravel-visualconsole::failed-jobs', compact(
            'type',
            'user',
            'errors',
            'code',
            'action',
            'messages',
            'commands',
            'jobs',
            'total_jobs',
            'failed_jobs',
            'tables_count'
        ));
    }

    private function getTables()
    {
        $query = DB::select('SHOW TABLES');
        return collect($query)->map(fn ($table, $k) => collect($table)->values()->first());
    }
}