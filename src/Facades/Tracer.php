<?php

namespace Rosalana\Tracer\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Rosalana\Tracer\Models\TracerReport emit(\Rosalana\Tracer\Enums\TracerReportType $type = \Rosalana\Tracer\Enums\TracerReportType::CUSTOM, array $data = [])
 * @method static \Rosalana\Tracer\Models\TracerReport emitRoute(string $group, string $method, string $path, ?string $ip = null)
 * @method static \Rosalana\Tracer\Models\TracerReport emitException(\Throwable $exception)
 * @method static \Rosalana\Tracer\Models\TracerReport emitOutpostSend(\Rosalana\Core\Services\Outpost\Message $message)
 * @method static \Rosalana\Tracer\Models\TracerReport emitOutpostReceive(\Rosalana\Core\Services\Outpost\Message $message)
 * @method static \Rosalana\Tracer\Models\TracerReport emitBasecamp(string $direction, \Rosalana\Core\Services\Basecamp\Request $request, \Illuminate\Http\Client\Response $response)
 * @method static void report()
 * @method static \Rosalana\Core\Facades\Trace runtime()
 * 
 * @see \Rosalana\Tracer\Services\Tracer\Manager
 */
class Tracer extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'rosalana.tracer';
    }
}
