<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class LeadCall extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'lead_id',
        'status',
        'comment',
        'duration',
        'causer_id',
    ];

    protected $casts = [
        'duration' => 'integer',
    ];

    protected $appends = [
        'formatted_duration',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'comment', 'duration'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => "Call {$eventName}");
    }

    public function getLogNameToUse(): string
    {
        return 'lead_call';
    }

    public function causer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'causer_id');
    }

    public function lead(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Lead::class, 'lead_id');
    }

    public function getFormattedDurationAttribute(): string
    {
        return self::formatDuration($this->duration);
    }

    public static function formatDuration(?int $duration): string
    {
        if (! $duration) {
            return '-';
        }

        $hours = intdiv($duration, 3600);
        $minutes = intdiv($duration % 3600, 60);
        $seconds = $duration % 60;

        if ($hours > 0) {
            return sprintf('%dh %02dm %02ds', $hours, $minutes, $seconds);
        }

        return sprintf('%dm %02ds', $minutes, $seconds);
    }
}
