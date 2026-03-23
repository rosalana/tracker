<?php

namespace Rosalana\Tracker\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Rosalana\Core\Events\BasecampRequestSent;
use Rosalana\Core\Events\OutpostMessageReceived;
use Rosalana\Core\Events\OutpostMessageSent;
use Rosalana\Core\Facades\App;
use Rosalana\Tracker\Facades\Tracker;
use Rosalana\Tracker\Services\Tracker\Collector;
use Rosalana\Tracker\Services\Tracker\Report;
use Rosalana\Tracker\Services\Tracker\Scope;
use Rosalana\Tracker\Support\Fingerprint;

class RosalanaTrackerServiceProvider extends ServiceProvider
{
    /**
     * Register everything in the container.
     */
    public function register()
    {
        $this->app->singleton('rosalana.tracker', function () {
            return new \Rosalana\Tracker\Services\Tracker\Manager(new Collector(), new Scope());
        });

        $this->app->resolving('rosalana.basecamp', function (\Rosalana\Core\Services\Basecamp\Manager $manager) {
            $manager->registerService('tracker', new \Rosalana\Tracker\Services\Basecamp\TrackerService());
        });
    }

    /**
     * Boot services.
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../../database/migrations/' => database_path('migrations'),
        ], 'rosalana-tracker-migrations');

        if ($this->app->runningInConsole()) {
            $this->commands([
                \Rosalana\Tracker\Console\Commands\TrackerSendCommand::class,
            ]);
        }

        if (! App::config('tracker.enabled')) return;

        $this->registerRoutes();
        $this->registerSendSchedule();
        $this->registerUserLoggingListener();
        $this->registerServiceIntegrationListeners();

        if (!$this->app->runningInConsole()) {

            /**
             * Register Route Tracking Middleware
             */
            $this->registerRouteTracking();

            /**
             * Register Exception Tracking
             */
            $this->registerExceptionTracking();
        }
    }

    public function registerRouteTracking(): void
    {
        $this->app['router']->pushMiddlewareToGroup('web', \Rosalana\Tracker\Http\Middleware\WebRoutesTracking::class);
        $this->app['router']->pushMiddlewareToGroup('internal', \Rosalana\Tracker\Http\Middleware\InternalRoutesTracking::class);
        $this->app['router']->pushMiddlewareToGroup('api', \Rosalana\Tracker\Http\Middleware\ApiRoutesTracking::class);
    }

    public function registerExceptionTracking(): void
    {
        $this->app->make('Illuminate\Contracts\Debug\ExceptionHandler')
            ->reportable(function (\Throwable $e) {
                static $reporting = false;
                if ($reporting) {
                    return;
                }
                $reporting = true;
                try {
                    Tracker::report(new Report(
                        type: \Rosalana\Tracker\Enums\TrackerReportType::EXCEPTION,
                        level: \Rosalana\Tracker\Support\ExceptionLevelResolver::resolve($e),
                        fingerprint: \Rosalana\Tracker\Support\ExceptionFingerprint::make($e),
                        payload: [
                            'message' => $e->getMessage(),
                            'file' => $e->getFile(),
                            'line' => $e->getLine(),
                            'trace' => $e->getTraceAsString(),
                        ]
                    ));
                } finally {
                    $reporting = false;
                }
            });
    }

    public function registerSendSchedule(): void
    {
        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);

            $schedule->command('tracker:send')
                ->everyThirtyMinutes()
                ->withoutOverlapping()
                ->onOneServer();
        });
    }

    public function registerRoutes(): void
    {
        Route::middleware('internal')
            ->prefix('internal')
            ->group(function () {
                $this->loadRoutesFrom(__DIR__ . '/../../routes/internal.php');
            });
    }

    public function registerServiceIntegrationListeners(): void
    {
        Event::listen(function (OutpostMessageSent $event) {
            $message = $event->message;

            Tracker::scope()->setLink('outpost_correlation_id', $message->correlationId);

            Tracker::report(new Report(
                type: \Rosalana\Tracker\Enums\TrackerReportType::OUTPOST_SEND,
                payload: [
                    'name' => $message->name(),
                    'status' => $message->status(),
                    'targets' => $message->to,
                    'origin' => $message->from ?? App::slug(),
                    'correlationId' => $message->correlationId,
                ],
                fingerprint: Fingerprint::make("outpost", $message->name()),
            ));
        });

        Event::listen(function (OutpostMessageReceived $event) {
            $message = $event->message;

            Tracker::scope()->setLink('outpost_correlation_id', $message->correlationId);

            Tracker::report(new Report(
                type: \Rosalana\Tracker\Enums\TrackerReportType::OUTPOST_RECEIVE,
                payload: [
                    'name' => $message->name(),
                    'status' => $message->status(),
                    'targets' => $message->to ?? [App::slug()],
                    'origin' => $message->from,
                    'correlationId' => $message->correlationId,
                ],
                fingerprint: Fingerprint::make("outpost", $message->name()),
            ));
        });

        Event::listen(function (BasecampRequestSent $event) {
            $request = $event->request;
            $response = $event->response;

            $requestId = uniqid('basecamp_', true);
            Tracker::scope()->setLink('basecamp_request_id', $requestId);

            Tracker::report(new Report(
                type: \Rosalana\Tracker\Enums\TrackerReportType::BASECAMP,
                payload: [
                    'method' => $request->getMethod(),
                    'endpoint' => $request->getUrl(),
                    'target' => $request->getTarget(),
                    'from' => App::slug(),
                    'status' => $response->status(),
                ],
                fingerprint: Fingerprint::make("basecamp", $request->getUrl()),
            ));
        });
    }

    public function registerUserLoggingListener(): void
    {
        Event::listen(function (\Illuminate\Auth\Events\Authenticated $event) {
            $user = $event->user;

            $remoteId = App::context()->scope("user.{$user->id}")->get('remote_id');

            Tracker::configureScope(function (\Rosalana\Tracker\Services\Tracker\Scope $scope) use ($user, $remoteId): void {
                $scope->setUser([
                    'id' => $remoteId,
                    'name' => $user->name,
                    'email' => $user->email,
                ]);
            });
        });
    }
}
