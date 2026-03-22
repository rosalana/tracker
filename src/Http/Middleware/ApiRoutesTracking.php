<?php

namespace Rosalana\Tracker\Http\Middleware;

use Rosalana\Tracker\Contracts\RouteTracking;

class ApiRoutesTracking extends RoutesTracking implements RouteTracking
{
    public function group(): string
    {
        return 'api';
    }
}