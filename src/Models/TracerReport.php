<?php

namespace Rosalana\Tracer\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Rosalana\Core\Facades\App;
use Rosalana\Tracer\Enums\TracerReportType;
use Rosalana\Tracer\Facades\Tracer;

class TracerReport extends Model
{
    protected $table = 'tracer_reports';

    protected $fillable = [
        'type',
        'report_id',
        'local_user_id',
        'remote_user_id',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
        'type' => TracerReportType::class,
    ];

    protected $appends = [
        'app',
        'user_id',
    ];

    protected static function booted()
    {
        static::creating(function (TracerReport $report) {
            $report->report_id = Str::uuid()->toString();

            if ($user = auth()->user()) {
                $report->local_user_id = $user->id;
                $report->remote_user_id = App::context()->scope("user.{$user->id}")->get('remote_id');
            }
        });
    }

    public function getAppAttribute(): string
    {
        return App::slug();
    }

    public function getUserIdAttribute(): ?int
    {
        return $this->remote_user_id;
    }

    public function report(): void
    {
        Tracer::reportSingle($this);
    }

    public function reportIf(callable|bool $condition = true): void
    {
        if (is_callable($condition) ? $condition($this) : $condition) {
            $this->report();
        }
    }
}
