<?php

namespace Rosalana\Tracker\Console\Commands;

use Illuminate\Console\Command;
use Rosalana\Tracker\Facades\Tracker;

class TrackerSendCommand extends Command
{
    protected $signature = 'tracker:send';

    protected $description = 'Send tracker reports to Basecamp';

    public function handle()
    {
        Tracker::sendCaptured();

        $this->info('Tracker reports sent.');
    }
}