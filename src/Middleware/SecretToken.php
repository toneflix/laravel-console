<?php

namespace ToneflixCode\LaravelVisualConsole\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Validation\UnauthorizedException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use ToneflixCode\LaravelVisualConsole\HttpStatus;

class SecretToken
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
        $signature = $request->header(
            'X-Hub-Signature',
            $request->input('X-Hub-Signature'),
            $request->header(
                'X-Signature',
                $request->input('X-Signature')
            )
        );

        if (($signature = $signature) == null) {
            return abort(HttpStatus::UNPROCESSABLE_ENTITY, 'Header not set');
        }

        $signature_parts = explode('=', $signature);

        if (count($signature_parts) != 2) {
            return abort(HttpStatus::BAD_REQUEST, 'Invalid signature format');
        }

        $content = $request->getContent() ? $request->getContent() : $request->input('payload');

        $known_token = config('laravel-visualconsole.webhook_secret');
        $known_signature = hash_hmac($signature_parts[0] ?? 'sha1', $content, $known_token);

        if (!hash_equals($known_signature, $signature_parts[1])) {
            return abort(HttpStatus::FORBIDDEN, 'Could not verify request signature ' . $signature_parts[1]);
        }

        return $next($request);
    }
}
