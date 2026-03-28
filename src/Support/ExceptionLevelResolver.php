<?php

namespace Rosalana\Tracker\Support;

use Rosalana\Core\Facades\App;
use Rosalana\Tracker\Enums\TrackerReportLevel;

class ExceptionLevelResolver
{
    public static function resolve(\Throwable $exception): TrackerReportLevel
    {
        $emergencyExceptions = App::config('tracer.emergency_exceptions', []);
        $criticalExceptions = App::config('tracer.critical_exceptions', []);

        if (in_array(get_class($exception), $emergencyExceptions, true)) {
            return TrackerReportLevel::EMERGENCY;
        }

        if (in_array(get_class($exception), $criticalExceptions, true)) {
            return TrackerReportLevel::CRITICAL;
        }

        if ($exception instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
            return match (true) {
                $exception->getStatusCode() >= 500 => TrackerReportLevel::ERROR,
                $exception->getStatusCode() >= 400 => TrackerReportLevel::WARNING,
                default => TrackerReportLevel::INFO,
            };
        }

        if ($exception instanceof \Rosalana\Core\Exceptions\Http\RosalanaHttpException) {
            return match (true) {
                $exception->getCode() >= 500 => TrackerReportLevel::CRITICAL,
                $exception->getCode() >= 400 => TrackerReportLevel::WARNING,
                default => TrackerReportLevel::INFO,
            };
        }

        if ($exception instanceof \Illuminate\Validation\ValidationException) {
            return TrackerReportLevel::WARNING;
        }

        if ($exception instanceof \Illuminate\Auth\AuthenticationException) {
            return TrackerReportLevel::WARNING;
        }

        if ($exception instanceof \Illuminate\Auth\Access\AuthorizationException) {
            return TrackerReportLevel::WARNING;
        }

        if ($exception instanceof \Error) {
            return TrackerReportLevel::CRITICAL;
        }

        return TrackerReportLevel::ERROR;
    }
}
