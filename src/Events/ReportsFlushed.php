<?php

namespace Rosalana\Tracker\Events;

use Illuminate\Database\Eloquent\Collection;

class ReportsFlushed
{
    public function __construct(
        public readonly Collection $reports,
    ) {}
}
