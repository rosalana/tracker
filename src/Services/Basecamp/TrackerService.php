<?php

namespace Rosalana\Tracker\Services\Basecamp;

use Illuminate\Http\Client\Response;
use Rosalana\Core\Services\Basecamp\Service;

class TrackerService extends Service
{
    public function report(array $data): Response
    {
        return $this->manager->post('/tracker/report', $data);
    }

    public function sync(array $data): Response
    {
        return $this->manager->post('/tracker/sync', $data);
    }
}
