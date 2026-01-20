<?php

namespace Rosalana\Tracker\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Rosalana\Core\Facades\App;
use Rosalana\Tracker\Facades\Tracker;

class TrackerController
{
    public function send(Request $request): JsonResponse
    {
        if (! App::config('tracker.enabled')) {
            return error()->badRequest('Tracker is disabled.')();
        }

        Tracker::sendCaptured();

        return ok()();
    }

    public function flush(Request $request): JsonResponse
    {
        if (! App::config('tracker.enabled')) {
            return error()->badRequest('Tracker is disabled.')();
        }

        Tracker::flushCaptured();

        return ok()();
    }
}
