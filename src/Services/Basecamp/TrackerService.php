<?php

namespace Rosalana\Tracker\Services\Basecamp;

use Illuminate\Http\Client\Response;
use Rosalana\Core\Facades\App;
use Rosalana\Core\Services\Basecamp\Service;

class TrackerService extends Service
{
    public function report(array $reports): Response
    {
        return $this->manager->post('/tracker/report', [
            'app' => App::slug(),
            'date' => now()->toDateString(),
            'reports' => $reports,
        ]);
    }
}
