<?php

namespace ToneflixCode\LaravelVisualConsole;

use Illuminate\Support\Facades\Facade;

/**
 * @see \ToneflixCode\LaravelVisualConsole\Skeleton\SkeletonClass
 */
class LaravelVisualConsoleFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'laravel-visualconsole';
    }
}