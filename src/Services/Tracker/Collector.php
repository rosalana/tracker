<?php

namespace Rosalana\Tracker\Services\Tracker;

use Rosalana\Core\Facades\Basecamp;
use Rosalana\Tracker\Events\ReportChunkDispatched;
use Rosalana\Tracker\Events\ReportCollected;
use Rosalana\Tracker\Events\ReportDispatchedImmediately;
use Rosalana\Tracker\Events\ReportsFlushed;
use Rosalana\Tracker\Models\TrackerReport;

class Collector
{
    /**
     * Collects an event and decides whether to send it immediately or defer it.
     * 
     * @param Report $report The event to be collected.
     * @return void
     */
    public function collect(Report $report): void
    {
        if ($report->shouldSendImmediate()) {
            $this->collectImmediate($report);
        } else {
            $this->save($report);
        }
    }

    /**
     * Sends a report immediately with fallback to deferred storage.
     * 
     * @param Report $report The event to be collected.
     * @return void
     */
    public function collectImmediate(Report $report): void
    {
        Basecamp::onFail(fn() => $this->save($report))
            ->onSuccess(fn() => event(new ReportDispatchedImmediately($report)))
            ->tracker()
            ->report([$report->toArray()]);
    }

    /**
     * Sends all deferred reports to Basecamp.
     * 
     * @return void
     */
    public function sendCollected(): void
    {
        TrackerReport::unsent()
            ->orderBy('created_at')
            ->chunkById(1000, function ($reports) {

                $data = $reports->map(fn($report) => $report->toArray())->toArray();

                Basecamp::onFail(fn() => false)
                    ->onSuccess(function () use ($reports) {
                        $reports->each(fn($report) => $report->markAsSent());
                        event(new ReportChunkDispatched($reports));
                    })
                    ->tracker()
                    ->report($data);
            });

        $this->cleanup();
    }

    /**
     * Cleans up sent reports from the database.
     */
    public function cleanup(): void
    {
        $sent = TrackerReport::sent();

        event(new ReportsFlushed($sent->get()));

        $sent->delete();
    }

    /**
     * Flushes all collected reports from the database.
     */
    public function flush(): void
    {
        TrackerReport::query()->delete();
    }

    /**
     * Saves a report to the database and fires the ReportCollected event.
     * 
     * @param Report $report The report to be saved.
     * @return void
     */
    private function save(Report $report): void
    {
        event(new ReportCollected($report));
        TrackerReport::create($report->toArray());
    }
}
