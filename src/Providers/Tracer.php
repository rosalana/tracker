<?php

namespace Rosalana\Tracer\Providers;

use Rosalana\Core\Contracts\Package;

class Tracer implements Package
{
    public function resolvePublished(): bool
    {
        return true;
    }

    public function publish(): array
    {
        return [];
    }
}
