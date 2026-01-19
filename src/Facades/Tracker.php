<?php

namespace Rosalana\Tracker\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void configureScope(\Closure $callback)
 * @method static \Rosalana\Tracker\Services\Tracker\Scope scope()
 * @method static void report(\Rosalana\Tracker\Services\Tracker\Report $report)
 * @method static void reportImmediate(\Rosalana\Tracker\Services\Tracker\Report $report)
 * @method static void sendCaptured()
 * 
 * @see \Rosalana\Tracker\Services\Tracker\Manager
 */
class Tracker extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'rosalana.tracker';
    }
}
