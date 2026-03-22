<?php

namespace Rosalana\Tracker\Support;

use Rosalana\Tracker\Facades\Tracker;

class ExceptionFingerprint
{
    public static function make(\Throwable $exception): string
    {
        $route = Tracker::scope()->getContext()['route'] ?? null;
        $routeStr = is_array($route) ? ($route['name'] ?? 'unknown') : ($route ?? 'unknown');

        return md5(sprintf(
            '%s|%s:%d|route=%s',
            get_class($exception),
            $exception->getFile(),
            $exception->getLine(),
            $routeStr,
        ));
    }
}