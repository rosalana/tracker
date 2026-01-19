<?php

namespace Rosalana\Tracker\Http\Middleware;

use Rosalana\Tracker\Contracts\RouteTracking;

class WebRoutesTracking implements RouteTracking
{
    public function group(): string
    {
        return 'web';
    }
}