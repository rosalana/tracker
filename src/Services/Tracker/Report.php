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
        public LogLevel $level = LogLevel::INFO,
        public ?string $fingerprint = null,
        public array $metrics = [],
    ) {
        $this->reportId = uniqid('report_', true);
    }

    public function attachScope(array $scope): void
    {
        $this->scope = $scope;
    }

    public function setLevel(LogLevel $level): void
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
        ]);
    }

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
