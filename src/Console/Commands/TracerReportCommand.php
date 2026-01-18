<?php

namespace Rosalana\Tracer\Console\Commands;

use Illuminate\Console\Command;
use Rosalana\Tracer\Facades\Tracer;

class TracerReportCommand extends Command
{
    protected $signature = 'tracer:report';

    protected $description = 'Send tracer reports to Basecamp';

    public function handle()
    {
        Tracer::report();

        $this->info('Tracer reports sent.');
    }
}