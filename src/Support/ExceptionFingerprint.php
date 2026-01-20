<?php

namespace Rosalana\Tracker\Support;

use Rosalana\Tracker\Facades\Tracker;

class ExceptionFingerprint
{
    public static function make(\Throwable $exception): string
    {
        return md5(sprintf(
            '%s|%s:%d|route=%s',
            get_class($exception),
            $exception->getFile(),
            $exception->getLine(),
            Tracker::scope()->getContext()['route'] ?? 'unknown',
        ));
    }
}