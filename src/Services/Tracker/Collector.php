<?php

namespace Rosalana\Tracker\Services\Tracker;

use Rosalana\Core\Facades\App;
use Rosalana\Core\Facades\Basecamp;
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
            App::hooks()->run('tracker:collect', ['report' => $report]);
            TrackerReport::create($report->toArray());
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
        App::hooks()->run('tracker:immediate', ['report' => $report]);

        Basecamp::fallback(fn() => $this->collect($report))
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
            ->chunk(100, function ($reports) {

                $data = $reports->map(fn($report) => $report->toArray())->toArray();
                $response = Basecamp::fallback(fn() => null)->tracker()->report($data);

                if ($response && $response->successful()) {
                    $reports->each(fn($report) => $report->markAsSent());
                }
            });

        $this->cleanup();
    }

    /**
     * Cleans up sent reports from the database.
     */
    public function cleanup(): void
    {
        $sent = TrackerReport::sent();

        App::hooks()->run('tracker:flush', ['reports' => $sent]);

        $sent->delete();
    }
}
