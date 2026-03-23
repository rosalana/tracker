<?php

namespace Rosalana\Tracker\Events;

use Illuminate\Database\Eloquent\Collection;

class ReportChunkDispatched
{
    public function __construct(
        public readonly Collection $reports
    ) {}
}
