<?php

namespace Rosalana\Tracker\Support;

use Psr\Log\LogLevel;

class ExceptionLevelResolver
{
    public static function resolve(\Throwable $exception): string
    {
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
