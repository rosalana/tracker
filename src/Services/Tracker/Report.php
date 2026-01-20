<?php

namespace Rosalana\Tracker\Services\Tracker;

use Psr\Log\LogLevel;
use Rosalana\Tracker\Enums\TrackerReportType;

class Report
{
    protected array $scope = [];
    protected string $reportId;

    public function __construct(
        public TrackerReportType $type,
        public array $payload = [],
        public string $level = LogLevel::INFO,
        public ?string $fingerprint = null,
        public array $metrics = [],
    ) {
        $this->reportId = uniqid('report_', true);
    }

    /**
     * Attaches scope snapshot information to the report.
     * 
     * @param array $scope
     * @return void
     */
    public function attachScope(array $scope): void
    {
        $this->scope = $scope;
    }

    /**
     * Sets the log level for the report.
     * 
     * @param string $level
     * @return void
     */
    public function setLevel(string $level): void
    {
        $this->level = $level;
    }

    /**
     * Determines if the report should be sent immediately based on its log level.
     * 
     * @return bool True if the report should be sent immediately, false otherwise.
     */
    public function shouldSendImmediate(): bool
    {
        return in_array($this->level, [
            LogLevel::EMERGENCY,
            LogLevel::ALERT,
            LogLevel::CRITICAL,
            LogLevel::ERROR,
        ], true);
    }

    /**
     * Converts the report to an array representation.
     * 
     * @return array
     */
    public function toArray(): array
    {
        return [
            'report_id' => $this->reportId,
            'type' => $this->type->value,
            'payload' => $this->payload,
            'level' => $this->level,
            'fingerprint' => $this->fingerprint,
            'metrics' => $this->metrics,
            'scope' => $this->scope,
        ];
    }
}
