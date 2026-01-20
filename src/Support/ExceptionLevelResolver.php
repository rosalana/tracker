<?php

namespace Rosalana\Tracker\Support;

use Psr\Log\LogLevel;
use Rosalana\Core\Facades\App;

class ExceptionLevelResolver
{
    public static function resolve(\Throwable $exception): string
    {
        $emergencyExceptions = App::config('tracer.emergency_exceptions', []);
        $criticalExceptions = App::config('tracer.critical_exceptions', []);

        if (in_array(get_class($exception), $emergencyExceptions, true)) {
            return LogLevel::EMERGENCY;
        }

        if (in_array(get_class($exception), $criticalExceptions, true)) {
            return LogLevel::CRITICAL;
        }

        if ($exception instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
            return match (true) {
                $exception->getStatusCode() >= 500 => LogLevel::ERROR,
                $exception->getStatusCode() >= 400 => LogLevel::WARNING,
                default => LogLevel::INFO,
            };
        }

        if ($exception instanceof \Rosalana\Core\Exceptions\Http\RosalanaHttpException) {
            return match (true) {
                $exception->getCode() >= 500 => LogLevel::CRITICAL,
                $exception->getCode() >= 400 => LogLevel::WARNING,
                default => LogLevel::INFO,
            };
        }

        if ($exception instanceof \Illuminate\Validation\ValidationException) {
            return LogLevel::WARNING;
        }

        if ($exception instanceof \Illuminate\Auth\AuthenticationException) {
            return LogLevel::WARNING;
        }

        if ($exception instanceof \Illuminate\Auth\Access\AuthorizationException) {
            return LogLevel::WARNING;
        }

        if ($exception instanceof \Error) {
            return LogLevel::CRITICAL;
        }

        return LogLevel::ERROR;
    }
}
