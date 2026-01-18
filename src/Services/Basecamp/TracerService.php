<?php

namespace Rosalana\Tracer\Services\Basecamp;

use Rosalana\Core\Facades\App;
use Rosalana\Core\Services\Basecamp\Service;

class TracerService extends Service
{
    public function report(array $data): void
    {
        $this->manager->post('/tracer/report', $data);
    }

    public function sync(array $data): void
    {
        $this->manager->post('/tracer/sync', $data);
    }
}
