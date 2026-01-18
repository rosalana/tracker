<?php

namespace Rosalana\Tracker\Console\Commands;

use Illuminate\Console\Command;
use Rosalana\Tracker\Facades\Tracker;

class TrackerReportCommand extends Command
{
    protected $signature = 'tracker:report';

    protected $description = 'Send tracker reports to Basecamp';

    public function handle()
    {
        Tracker::report();

        $this->info('Tracker reports sent.');
    }
}