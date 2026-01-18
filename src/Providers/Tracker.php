<?php

namespace Rosalana\Tracker\Providers;

use Illuminate\Support\Facades\Artisan;
use Rosalana\Configure\Configure;
use Rosalana\Core\Contracts\Package;

class Tracker implements Package
{
    public function resolvePublished(): bool
    {
        return Configure::file('rosalana')->has('tracer.enabled');
    }

    public function publish(): array
    {
        return [
            'config' => [
                'label' => 'Publish config to rosalana config file',
                'run' => function () {
                    Configure::file('rosalana')

                        ->section('.tracer')
                        ->withComment(
                            'Trace System',
                            "Configuration for the internal tracing system used for performance monitoring and debugging.",
                        )
                        ->value('enabled', false)
                        ->value('critical_exceptions', [
                            \Error::class,
                            \PDOException::class,
                        ])

                        ->save();
                }
            ],
            'migrations' => [
                'label' => 'Publish database migrations',
                'run' => function () {
                    Artisan::call('vendor:publish', [
                        '--tag' => 'rosalana-tracker-migrations',
                        '--force' => true
                    ]);
                }
            ],
        ];
    }
}
