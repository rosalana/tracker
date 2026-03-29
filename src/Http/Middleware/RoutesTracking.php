<?php

namespace Rosalana\Tracker\Http\Middleware;

use Illuminate\Http\Request;
use Rosalana\Core\Facades\App;
use Rosalana\Tracker\Contracts\RouteTracking;
use Rosalana\Tracker\Enums\TrackerReportLevel;
use Rosalana\Tracker\Facades\Tracker;
use Rosalana\Tracker\Services\Tracker\Report;
use Symfony\Component\HttpFoundation\Response;

class RoutesTracking implements RouteTracking
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, \Closure $next): Response
    {
        Tracker::configureScope(function (\Rosalana\Tracker\Services\Tracker\Scope $scope) use ($request) {
            $scope->setContext('route', [
                'group' => $this->group(),
                'name' => optional($request->route())->getName(),
                'method' => $request->method(),
                'path' => $request->path(),
            ]);

            if ($request->hasSession()) {
                $scope->setLink('session_id', $request->session()->getId());
            }
        });

        $start = microtime(true);

        try {
            $response = $next($request);
        } catch (\Throwable $e) {
            $response = null;
            throw $e;
        } finally {

            $duration = microtime(true) - $start;

            Tracker::report(new Report(
                type: \Rosalana\Tracker\Enums\TrackerReportType::ROUTE,
                level: $this->reportLevelByDuration($duration),
                payload: [
                    'status_code' => $response?->getStatusCode() ?? 500,
                ],
                metrics: [
                    'duration_ms' => (int) ($duration * 1000),
                ],
            ));
        }

        return $response;
    }

    protected function reportLevelByDuration(float $duration): TrackerReportLevel
    {
        $critical_threshold = App::config('tracker.route_threshold.critical', 4);
        $alert_threshold = App::config('tracker.route_threshold.alert', 2);
        $warning_threshold = App::config('tracker.route_threshold.warning', 1);

        if ($duration >= $critical_threshold) {
            return TrackerReportLevel::CRITICAL;
        }

        if ($duration >= $alert_threshold) {
            return TrackerReportLevel::ALERT;
        }

        if ($duration >= $warning_threshold) {
            return TrackerReportLevel::WARNING;
        }

        return TrackerReportLevel::INFO;
    }

    public function group(): string
    {
        return 'all';
    }
}
