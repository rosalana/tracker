<?php

namespace Rosalana\Tracker\Services\Tracker;

use Rosalana\Core\Facades\App;
use Rosalana\Core\Facades\Basecamp;
use Rosalana\Core\Facades\Trace;
use Rosalana\Tracker\Enums\TrackerReportType;
use Rosalana\Tracker\Models\TrackerReport;

class Manager
{
    public function emit(TrackerReportType $type = TrackerReportType::CUSTOM, array $data = []): TrackerReport
    {
        return TrackerReport::create([
            'type' => $type,
            'data' => $data,
        ]);
    }

    public function emitRoute(string $group, string $method, string $path, ?string $ip = null): TrackerReport
    {
        return $this->emit(TrackerReportType::ROUTE, [
            'group' => $group,
            'method' => $method,
            'path' => $path,
            'ip' => $ip,
        ]);
    }

    public function emitException(\Throwable $exception): TrackerReport
    {
        $report = $this->emit(TrackerReportType::EXCEPTION, [
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
        ]);

        if ($this->isCriticalException($exception)) {
            $this->reportSingle($report);
        }

        return $report;
    }


    /**
     * @param \Rosalana\Core\Services\Outpost\Message $message
     */
    public function emitOutpostSend($message): TrackerReport
    {
        return $this->emit(TrackerReportType::OUTPOST, [
            'direction' => 'send',
            'message' => $message->toArray(),
        ]);
    }

    /** 
     * @param \Rosalana\Core\Services\Outpost\Message $message 
     */
    public function emitOutpostReceive($message): TrackerReport
    {
        return $this->emit(TrackerReportType::OUTPOST, [
            'direction' => 'receive',
            'message' => $message->toArray(),
        ]);
    }

    /** 
     * @param \Rosalana\Core\Services\Basecamp\Request $request 
     * @param \Illuminate\Http\Client\Response $response
     */
    public function emitBasecamp($request, $response): TrackerReport
    {
        return $this->emit(TrackerReportType::BASECAMP, [
            'request' => [
                'method' => $request->getMethod(),
                'endpoint' => $request->getUrl(),
                'to' => $request->getTarget(),
            ],
            'response' => [
                'status' => $response->status(),
            ],
        ]);
    }

    public function runtime(): Trace
    {
        return app(Trace::class);
    }

    public function report(): void
    {
        TrackerReport::query()
            ->orderBy('id')
            ->chunkById(100, function ($reports) {
                $payload = $reports->map->toArray()->toArray();

                try {
                    $response = Basecamp::tracker()->sync($payload);

                    if ($response->successful()) {
                        TrackerReport::whereIn('id', $reports->pluck('id'))->delete();
                    }
                } catch (\Throwable $e) {
                    // Silently fail - reports will be retried on next sync
                    report($e);
                }
            });
    }

    public function reportSingle(TrackerReport $TrackerReport): void
    {
        if (!$TrackerReport->exists) {
            return;
        }

        try {
            $response = Basecamp::tracker()->report($TrackerReport->toArray());

            if ($response->successful()) {
                $TrackerReport->delete();
            }
        } catch (\Throwable $e) {
            // Silently fail - report will be sent on next batch sync
            report($e);
        }
    }

    private function isCriticalException(\Throwable $e): bool
    {
        $criticalExceptions = App::config('tracer.critical_exceptions', []);

        foreach ($criticalExceptions as $exceptionClass) {
            if ($e instanceof $exceptionClass) {
                return true;
            }
        }

        return false;
    }
}
