<?php

namespace ToneflixCode\LaravelVisualConsole\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (is_array($roles = $user[config('laravel-visualconsole.permission_field')]) &&
            in_array(config('laravel-visualconsole.permission_value'), $roles)) {
            return $next($request);
        } elseif ($user[config('laravel-visualconsole.permission_field')] == config('laravel-visualconsole.permission_value')) {
            return $next($request);
        } elseif (!config('laravel-visualconsole.permission_field') || !config('laravel-visualconsole.permission_value')) {
            return $next($request);
        }

        return abort(403, 'Unauthorized action');
    }
}