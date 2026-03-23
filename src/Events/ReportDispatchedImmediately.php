<?php

namespace Rosalana\Tracker\Events;

use Rosalana\Tracker\Services\Tracker\Report;

class ReportDispatchedImmediately
{
    public function __construct(
        public readonly Report $report,
    ) {}
}
