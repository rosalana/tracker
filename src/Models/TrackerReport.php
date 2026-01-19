<?php

namespace Rosalana\Tracker\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Rosalana\Tracker\Enums\TrackerReportType;

class TrackerReport extends Model
{
    protected $table = 'tracker_reports';

    protected $fillable = [
        'report_id',
        'type',
        'payload',
        'level',
        'fingerprint',
        'metrics',
        'scope',
        'sent_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'metrics' => 'array',
        'scope' => 'array',
        'type' => TrackerReportType::class,
    ];

    public function scopeSent(Builder $query)
    {
        return $query->whereNotNull('sent_at');
    }

    public function scopeUnsent(Builder $query)
    {
        return $query->whereNull('sent_at');
    }

    public function markAsSent(): void
    {
        $this->sent_at = now();
        $this->save();
    }
}
