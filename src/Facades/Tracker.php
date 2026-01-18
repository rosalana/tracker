<?php

namespace Rosalana\Tracker\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Rosalana\Tracker\Models\TrackerReport emit(\Rosalana\Tracker\Enums\TrackerReportType $type = \Rosalana\Tracker\Enums\TrackerReportType::CUSTOM, array $data = [])
 * @method static \Rosalana\Tracker\Models\TrackerReport emitRoute(string $group, string $method, string $path, ?string $ip = null)
 * @method static \Rosalana\Tracker\Models\TrackerReport emitException(\Throwable $exception)
 * @method static \Rosalana\Tracker\Models\TrackerReport emitOutpostSend(\Rosalana\Core\Services\Outpost\Message $message)
 * @method static \Rosalana\Tracker\Models\TrackerReport emitOutpostReceive(\Rosalana\Core\Services\Outpost\Message $message)
 * @method static \Rosalana\Tracker\Models\TrackerReport emitBasecamp(\Rosalana\Core\Services\Basecamp\Request $request, \Illuminate\Http\Client\Response $response)
 * @method static void report()
 * @method static void reportSingle(\Rosalana\Tracker\Models\TrackerReport $TrackerReport)
 * @method static \Rosalana\Core\Facades\Trace runtime()
 *
 * @see \Rosalana\Tracker\Services\Tracker\Manager
 */
class Tracker extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'rosalana.tracker';
    }
}
