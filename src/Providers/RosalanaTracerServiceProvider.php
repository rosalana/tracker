<?php

namespace Rosalana\Tracer\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;
use Rosalana\Core\Facades\App;
use Rosalana\Tracer\Facades\Tracer;

class RosalanaTracerServiceProvider extends ServiceProvider
{
    /**
     * Register everything in the container.
     */
    public function register()
    {
        $this->app->singleton('rosalana.tracer', function () {
            return new \Rosalana\Tracer\Services\Tracer\Manager();
        });

        $this->app->resolving('rosalana.basecamp', function (\Rosalana\Core\Services\Basecamp\Manager $manager) {
            $manager->registerService('tracer', new \Rosalana\Tracer\Services\Basecamp\TracerService());
        });

        if (App::config('tracer.enabled') !== true) return;

        App::hooks()->onOutpostSend(function (array $data) {
            /** @var \Rosalana\Core\Services\Outpost\Message $message */
            $message = $data['message'];

            Tracer::emitOutpostSend($message);
        });

        App::hooks()->onOutpostReceive(function (array $data) {
            /** @var \Rosalana\Core\Services\Outpost\Message $message */
            $message = $data['message'];

            Tracer::emitOutpostReceive($message);
        });

        App::hooks()->onBasecampSend(function (array $data) {
            /** @var \Rosalana\Core\Services\Basecamp\Request $request */
            $request = $data['request'];
            /** @var \Illuminate\Http\Client\Response $response */
            $response = $data['response'];

            Tracer::emitBasecamp($request, $response);
        });
    }

    /**
     * Boot services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                \Rosalana\Tracer\Console\Commands\TracerReportCommand::class,
            ]);
        }

        $this->publishes([
            __DIR__ . '/../../database/migrations/' => database_path('migrations'),
        ], 'rosalana-tracer-migrations');

        if (App::config('tracer.enabled') !== true) return;

        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);

            $schedule->command('tracer:report')
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
        $this->app['router']->pushMiddlewareToGroup('web', \Rosalana\Tracer\Http\Middleware\WebRoutesTracking::class);
        $this->app['router']->pushMiddlewareToGroup('internal', \Rosalana\Tracer\Http\Middleware\InternalRoutesTracking::class);
        $this->app['router']->pushMiddlewareToGroup('api', \Rosalana\Tracer\Http\Middleware\ApiRoutesTracking::class);
    }

    public function registerExceptionTracking(): void
    {
        $this->app->make('Illuminate\Contracts\Debug\ExceptionHandler')
            ->register(function (\Throwable $e) {
                Tracer::emitException($e);
            });
    }
}
