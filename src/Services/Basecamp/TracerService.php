<?php

namespace Rosalana\Tracer\Services\Basecamp;

use Illuminate\Http\Client\Response;
use Rosalana\Core\Services\Basecamp\Service;

class TracerService extends Service
{
    public function report(array $data): Response
    {
        return $this->manager->post('/tracer/report', $data);
    }

    public function sync(array $data): Response
    {
        return $this->manager->post('/tracer/sync', $data);
    }
}
