<?php

namespace App\Models;

use App\Traits\HasBranch;
use App\Traits\UsesDynamicConnection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sms extends Model
{
    use HasBranch, SoftDeletes, UsesDynamicConnection;

    // status: pending, sent, failed
    // eskiz_status: pending, sent, failed
    protected $fillable = [
        'phone',
        'content',
        'status',
        'sent_at',
        'external_id',
        'gateway_batch_id',
        'smsable_id',
        'smsable_type',
        'error_log',
        'character_count',
        'scheduled_at',
        'sms_group_id',
        'sms_batch_id',
        'sms_template_id',
        'channel',
        'eskiz_status',
        'result_date',
        'total_price',
        'price',
        'parts_count',
        'is_ad',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'result_date' => 'datetime',
        'total_price' => 'integer',
        'price' => 'integer',
        'parts_count' => 'integer',
        'is_ad' => 'boolean',
    ];

    public function smsable()
    {
        return $this->morphTo();
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(SmsBatch::class, 'sms_batch_id');
    }

    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function getSmsTypeAttribute(): string
    {
        return match ($this->smsable_type) {
            User::class => 'staff',
            Student::class => 'student',
            Lead::class => 'lead',
            default => 'unknown'
        };
    }
}
