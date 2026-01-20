<?php

namespace Rosalana\Tracker\Console\Commands;

use Illuminate\Console\Command;
use Rosalana\Core\Facades\App;
use Rosalana\Tracker\Facades\Tracker;

class TrackerSendCommand extends Command
{
    protected $signature = 'tracker:send';

    protected $description = 'Send tracker reports to Basecamp';

    public function handle()
    {
        if (! App::config('tracker.enabled')) {
            $this->error('Tracker is disabled in the configuration.');
            return;
        }

        Tracker::sendCaptured();

        $this->info('Tracker reports sent.');
    }
}
