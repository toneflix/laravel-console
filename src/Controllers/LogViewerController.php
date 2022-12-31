<?php

namespace ToneflixCode\LaravelVisualConsole\Controllers;

use Illuminate\Support\Facades\Crypt;
use \Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Rap2hpoutre\LaravelLogViewer\LaravelLogViewer;

/**
 * Class LogViewerController
 * @package Rap2hpoutre\LaravelLogViewer
 */
class LogViewerController extends BaseController
{
    /**
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * @var LaravelLogViewer
     */
    private $log_viewer;

    /**
     * @var string
     */
    protected $view_log = 'laravel-visualconsole::laravel-log-viewer.log';

    /**
     * @var array
     */
    private $levels_classes = [
        'debug' => 'blue',
        'info' => 'blue',
        'notice' => 'blue',
        'warning' => 'warning',
        'error' => 'red',
        'critical' => 'red',
        'alert' => 'red',
        'emergency' => 'red',
        'processed' => 'blue',
        'failed' => 'warning',
    ];

    /**
     * @var array
     */
    private $levels_imgs = [
        'debug' => 'information',
        'info' => 'information',
        'notice' => 'information',
        'warning' => 'alert',
        'error' => 'alert',
        'critical' => 'alert',
        'alert' => 'alert',
        'emergency' => 'alert',
        'processed' => 'information',
        'failed' => 'alert'
    ];

    /**
     * LogViewerController constructor.
     */
    public function __construct()
    {
        $this->log_viewer = new LaravelLogViewer();
        $this->request = app('request');
    }

    /**
     * @return array|mixed
     * @throws \Exception
     */
    public function index()
    {
        $folderFiles = [];
        if ($this->request->input('f')) {
            $this->log_viewer->setFolder(Crypt::decrypt($this->request->input('f')));
            $folderFiles = $this->log_viewer->getFolderFiles(true);
        }
        if ($this->request->input('l')) {
            $this->log_viewer->setFile(Crypt::decrypt($this->request->input('l')));
        }

        if ($early_return = $this->earlyReturn()) {
            return $early_return;
        }

        $paginated = collect($this->log_viewer->all())->map(function ($log) {
                // Extract the stack trace
            $stack = explode('[stacktrace]', $log['stack']);
            $log['stack'] = explode('#', trim($stack[1]??''));
            $log['level_class'] = $this->levels_classes[$log['level']];
            $log['level_img'] = 'ri-'.$this->levels_imgs[$log['level']].'-fill';
            array_shift($log['stack']);
            array_pop($log['stack']);
            return $log;
        });

        $data = [
            'logs' => $paginated->paginator(3),
            'folders' => $this->log_viewer->getFolders(),
            'current_folder' => $this->log_viewer->getFolderName(),
            'folder_files' => $folderFiles,
            'files' => $this->log_viewer->getFiles(true),
            'current_file' => $this->log_viewer->getFileName(),
            'standardFormat' => true,
            'structure' => $this->log_viewer->foldersAndFiles(),
            'storage_path' => $this->log_viewer->getStoragePath(),

        ];

        if ($this->request->wantsJson()) {
            return $data;
        }

        if (is_array($data['logs']) && count($data['logs']) > 0) {
            $firstLog = reset($data['logs']);
            if (!$firstLog['context'] && !$firstLog['level']) {
                $data['standardFormat'] = false;
            }
        }

        $user = Auth::user();
        $errors = session()->get('errors');
        $action = session()->get('action');
        $messages = session()->get('messages');

        $data = array_merge(compact(
            'user',
            'errors',
            'action',
            'messages',
        ), $data);

        return app('view')->make($this->view_log, $data);
    }

    /**
     * @return bool|mixed
     * @throws \Exception
     */
    private function earlyReturn()
    {
        if ($this->request->input('f')) {
            $this->log_viewer->setFolder(Crypt::decrypt($this->request->input('f')));
        }

        if ($this->request->input('dl')) {
            return $this->download($this->pathFromInput('dl'));
        } elseif ($this->request->has('clean')) {
            app('files')->put($this->pathFromInput('clean'), '');
            return $this->redirect(url()->previous());
        } elseif ($this->request->has('del')) {
            app('files')->delete($this->pathFromInput('del'));
            return $this->redirect($this->request->url());
        } elseif ($this->request->has('delall')) {
            $files = ($this->log_viewer->getFolderName())
                        ? $this->log_viewer->getFolderFiles(true)
                        : $this->log_viewer->getFiles(true);
            foreach ($files as $file) {
                app('files')->delete($this->log_viewer->pathToLogFile($file));
            }
            return $this->redirect($this->request->url());
        }
        return false;
    }

    /**
     * @param string $input_string
     * @return string
     * @throws \Exception
     */
    private function pathFromInput($input_string)
    {
        return $this->log_viewer->pathToLogFile(Crypt::decrypt($this->request->input($input_string)));
    }

    /**
     * @param $to
     * @return mixed
     */
    private function redirect($to)
    {
        if (function_exists('redirect')) {
            return redirect($to);
        }

        return app('redirect')->to($to);
    }

    /**
     * @param string $data
     * @return mixed
     */
    private function download($data)
    {
        if (function_exists('response')) {
            return response()->download($data);
        }

        // For laravel 4.2
        return app('\Illuminate\Support\Facades\Response')->download($data);
    }
}