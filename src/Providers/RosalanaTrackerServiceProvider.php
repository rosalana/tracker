<?php

namespace Rosalana\Tracker\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;
use Rosalana\Core\Facades\App;
use Rosalana\Tracker\Facades\Tracker;

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

        if (App::config('tracer.enabled') !== true) return;

        App::hooks()->onOutpostSend(function (array $data) {
            /** @var \Rosalana\Core\Services\Outpost\Message $message */
            $message = $data['message'];

            Tracker::emitOutpostSend($message);
        });

        App::hooks()->onOutpostReceive(function (array $data) {
            /** @var \Rosalana\Core\Services\Outpost\Message $message */
            $message = $data['message'];

            Tracker::emitOutpostReceive($message);
        });

        App::hooks()->onBasecampSend(function (array $data) {
            /** @var \Rosalana\Core\Services\Basecamp\Request $request */
            $request = $data['request'];
            /** @var \Illuminate\Http\Client\Response $response */
            $response = $data['response'];

            Tracker::emitBasecamp($request, $response);
        });
    }

    /**
     * Boot services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                \Rosalana\Tracker\Console\Commands\TrackerReportCommand::class,
            ]);
        }

        $this->publishes([
            __DIR__ . '/../../database/migrations/' => database_path('migrations'),
        ], 'rosalana-tracker-migrations');

        if (App::config('tracer.enabled') !== true) return;

        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);

            $schedule->command('tracker:report')
                ->everyFiveMinutes()
                ->withoutOverlapping()
                ->onOneServer();
        });

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
                Tracker::emitException($e);
            });
    }
}
