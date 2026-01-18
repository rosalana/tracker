<?php

namespace Rosalana\Tracer\Services\Tracer;

use Rosalana\Core\Facades\App;
use Rosalana\Core\Facades\Basecamp;
use Rosalana\Core\Facades\Trace;
use Rosalana\Tracer\Enums\TracerReportType;
use Rosalana\Tracer\Models\TracerReport;

class Manager
{
    public function emit(TracerReportType $type = TracerReportType::CUSTOM, array $data = []): TracerReport
    {
        return TracerReport::create([
            'type' => $type,
            'data' => $data,
        ]);
    }

    public function emitRoute(string $group, string $method, string $path, ?string $ip = null): TracerReport
    {
        return $this->emit(TracerReportType::ROUTE, [
            'group' => $group,
            'method' => $method,
            'path' => $path,
            'ip' => $ip,
        ]);
    }

    public function emitException(\Throwable $exception): TracerReport
    {
        $report = $this->emit(TracerReportType::EXCEPTION, [
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
    public function emitOutpostSend($message): TracerReport
    {
        return $this->emit(TracerReportType::OUTPOST, [
            'direction' => 'send',
            'message' => $message->toArray(),
        ]);
    }

    /** 
     * @param \Rosalana\Core\Services\Outpost\Message $message 
     */
    public function emitOutpostReceive($message): TracerReport
    {
        return $this->emit(TracerReportType::OUTPOST, [
            'direction' => 'receive',
            'message' => $message->toArray(),
        ]);
    }

    /** 
     * @param \Rosalana\Core\Services\Basecamp\Request $request 
     * @param \Illuminate\Http\Client\Response $response
     */
    public function emitBasecamp($request, $response): TracerReport
    {
        return $this->emit(TracerReportType::BASECAMP, [
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
        TracerReport::query()
            ->orderBy('id')
            ->chunkById(100, function ($reports) {
                $payload = $reports->map->toArray()->toArray();

                try {
                    $response = Basecamp::tracer()->sync($payload);

                    if ($response->successful()) {
                        TracerReport::whereIn('id', $reports->pluck('id'))->delete();
                    }
                } catch (\Throwable $e) {
                    // Silently fail - reports will be retried on next sync
                    report($e);
                }
            });
    }

    public function reportSingle(TracerReport $tracerReport): void
    {
        if (!$tracerReport->exists) {
            return;
        }

        try {
            $response = Basecamp::tracer()->report($tracerReport->toArray());

            if ($response->successful()) {
                $tracerReport->delete();
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
