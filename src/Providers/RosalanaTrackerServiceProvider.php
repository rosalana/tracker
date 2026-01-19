<?php

namespace Rosalana\Tracker\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;
use Rosalana\Core\Facades\App;
use Rosalana\Tracker\Facades\Tracker;
use Rosalana\Tracker\Services\Tracker\Report;

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

        if (App::config('tracker.enabled') !== true) return;

        App::hooks()->onOutpostSend(function (array $data) {
            /** @var \Rosalana\Core\Services\Outpost\Message $message */
            $message = $data['message'];
        });

        App::hooks()->onOutpostReceive(function (array $data) {
            /** @var \Rosalana\Core\Services\Outpost\Message $message */
            $message = $data['message'];
        });

        App::hooks()->onBasecampSend(function (array $data) {
            /** @var \Rosalana\Core\Services\Basecamp\Request $request */
            $request = $data['request'];
            /** @var \Illuminate\Http\Client\Response $response */
            $response = $data['response'];
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

        if (App::config('tracker.enabled') !== true) return;

        if ($this->app->runningInConsole()) {
            $this->commands([
                \Rosalana\Tracker\Console\Commands\TrackerSendCommand::class,
            ]);
        }

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
            Tracker::configureScope(function (\Rosalana\Tracker\Services\Tracker\Scope $scope) use ($user): void {
                $scope->setUser([
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ]);
            });
        });
    }
}
