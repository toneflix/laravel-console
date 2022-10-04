<?php

namespace ToneflixCode\LaravelConsole;

use Illuminate\Support\Facades\Facade;

/**
 * @see \ToneflixCode\LaravelConsole\Skeleton\SkeletonClass
 */
class LaravelConsoleFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'laravel-console';
    }
}
