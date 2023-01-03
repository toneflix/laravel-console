<?php

namespace ToneflixCode\LaravelVisualConsole;

use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\File;
use Winter\LaravelConfigWriter\EnvFile;

class LaravelVisualConsole
{

    public function assetFile($file)
    {
        return $this->privateFile($file);
    }

    public function privateFile($file)
    {
        // $src = base64url_decode($file);
        $src = urldecode($file);
        try {
            $load = File::get(__DIR__.'/../assets/'.$src);

            if (str($src)->contains('.css')) {
                $mime = 'text/css';
            } elseif (str($src)->contains('.js')) {
                $mime = 'text/javascript';
            } elseif (str($src)->contains('.png')) {
                $mime = 'image/png';
            } elseif (str($src)->contains('.jpg')) {
                $mime = 'image/jpg';
            } elseif (str($src)->contains('.gif')) {
                $mime = 'image/gif';
            } elseif (str($src)->contains('.svg')) {
                $mime = 'image/svg+xml';
            } else {
                $mime = File::mimeType(__DIR__.'/../assets/'.$src);
            }

            // create response and add encoded image data
            $response = Response::make($load);
            // set headers
            return $response->header('Content-Type', $mime)
                    ->header('Cross-Origin-Resource-Policy', 'cross-origin')
                    ->header('Access-Control-Allow-Origin', '*');
        } catch (\Throwable $th) {
            return abort(404, 'File not found');
        }
    }

    public function routes($url = null)
    {
        $routes = [
            ['to' => '.console.user', 'icon' => 'terminal-box', 'label' => 'Console', 'params' => []],
            ['to' => '.console.error.logs', 'icon' => 'file-list-3', 'label' => 'System Logs', 'params' => []],
            ['to' => '.console.jobs', 'icon' => 'task', 'label' => 'Scheduled Tasks', 'params' => []],
            ['to' => '.console.jobs', 'icon' => 'error-warning', 'label' => 'Failed Jobs', 'params' => ['failed']],
            ['to' => '.console.controls', 'icon' => 'database', 'label' => 'Backup Utility', 'params' => ['backup']],
        ];

        if ($url) {
            // Find the route by url
            foreach ($routes as $route) {
                if (route(config('laravel-visualconsole.route_prefix', 'system') . $route['to'], $route['params']) == $url) {
                    return $route;
                }
            }

            return ['to' => '.console.user', 'icon' => 'terminal-box', 'label' => 'Console', 'params' => []];
        }

        return $routes;
    }

    public function update_env( $data = [] ) : void
    {
        $env = EnvFile::open(base_path('.env'));
        collect($data)->each(function ($value, $key) use ($env) {
            if ($key) {
                $env->set($key, $value);
            }
        });
        $env->write();
    }
}
