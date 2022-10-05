<?php

namespace ToneflixCode\LaravelVisualConsole;

use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\File;

class LaravelVisualConsole
{

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
}
