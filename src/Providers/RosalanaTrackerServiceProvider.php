<?php

namespace Rosalana\Tracker\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;
use Rosalana\Core\Facades\App;
use Rosalana\Tracker\Facades\Tracker;
use Rosalana\Tracker\Services\Tracker\Report;
use Rosalana\Tracker\Support\Fingerprint;

class RosalanaTrackerServiceProvider extends ServiceProvider
{
    /**
     * Register everything in the container.
     */
    public function register()
    {
        $this->app->singleton('rosalana.tracker', function () {
            return new \Rosalana\Tracker\Services\Tracker\Manager();
        });

        $this->app->resolving('rosalana.basecamp', function (\Rosalana\Core\Services\Basecamp\Manager $manager) {
            $manager->registerService('tracker', new \Rosalana\Tracker\Services\Basecamp\TrackerService());
        });

        if (! App::config('tracker.enabled')) return;

        App::hooks()->onOutpostSend(function (array $data) {
            /** @var \Rosalana\Core\Services\Outpost\Message $message */
            $message = $data['message'];

            Tracker::scope()->setLink('outpost_correlation_id', $message->correlationId);

            Tracker::report(new Report(
                type: \Rosalana\Tracker\Enums\TrackerReportType::OUTPOST_SEND,
                payload: [
                    'namespace' => $message->namespace,
                    'targets' => $message->to,
                    'origin' => $message->from ?? App::slug(),
                    'correlationId' => $message->correlationId,
                ],
                fingerprint: Fingerprint::make("outpost", $message->name()),
            ));
        });

        App::hooks()->onOutpostReceive(function (array $data) {
            /** @var \Rosalana\Core\Services\Outpost\Message $message */
            $message = $data['message'];

            Tracker::scope()->setLink('outpost_correlation_id', $message->correlationId);

            Tracker::report(new Report(
                type: \Rosalana\Tracker\Enums\TrackerReportType::OUTPOST_RECEIVE,
                payload: [
                    'namespace' => $message->namespace,
                    'targets' => $message->to ?? [App::slug()],
                    'origin' => $message->from,
                    'correlationId' => $message->correlationId,
                ],
                fingerprint: Fingerprint::make("outpost", $message->name()),
            ));
        });

        App::hooks()->onBasecampSend(function (array $data) {
            /** @var \Rosalana\Core\Services\Basecamp\Request $request */
            $request = $data['request'];
            /** @var \Illuminate\Http\Client\Response $response */
            $response = $data['response'];

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

        $this->registerSendSchedule();
        $this->registerUserLoggingListener();

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
            ->register(function (\Throwable $e) {
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

    public function registerUserLoggingListener(): void
    {
        \Illuminate\Support\Facades\Event::listen(function (\Illuminate\Auth\Events\Authenticated $event) {
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
