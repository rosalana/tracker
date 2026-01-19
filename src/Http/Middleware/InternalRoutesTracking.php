<?php

namespace Rosalana\Tracker\Http\Middleware;

use Rosalana\Tracker\Contracts\RouteTracking;

class InternalRoutesTracking implements RouteTracking
{
    public function group(): string
    {
        return 'internal';
    }
}