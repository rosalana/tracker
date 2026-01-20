<?php

namespace Rosalana\Tracker\Services\Tracker;

class Manager
{
    private Scope $scope;

    private Collector $collector;

    public function __construct()
    {
        $this->collector = new Collector();
        $this->scope = new Scope();
    }

    /**
     * Gets the global scope.
     */
    public function scope(): Scope
    {
        return $this->scope;
    }

    /**
     * Configures the global scope using a callback.
     */
    public function configureScope(\Closure $callback): void
    {
        $this->scope->configure($callback);
    }

    /**
     * Captures a report to be sent later to Basecamp.
     */
    public function report(Report $report): void
    {
        $report->attachScope($this->scope->snapshot());
        $this->collector->collect($report);
    }

    /**
     * Sends a report immediately to Basecamp.
     */
    public function reportImmediate(Report $report): void
    {
        $report->attachScope($this->scope->snapshot());
        $this->collector->collectImmediate($report);
    }

    /**
     * Sends all captured reports to Basecamp.
     */
    public function sendCaptured(): void
    {
        $this->collector->sendCollected();
    }

    public function flushCaptured(): void
    {
        $this->collector->flush();
    }
}
