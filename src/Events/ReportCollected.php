<?php

namespace Rosalana\Tracker\Events;

use Rosalana\Tracker\Services\Tracker\Report;

class ReportCollected
{
    public function __construct(
        public readonly Report $report,
    ) {}
}
