<?php

namespace App\Models;

use App\Traits\UsesDynamicConnection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SmsBatch extends Model
{
    use HasFactory, UsesDynamicConnection;

    protected $fillable = [
        'sms_group_id',
        'sms_template_id',
        'branch_id',
        'created_by_id',
        'source',
        'send_sms_type',
        'recipient_scope',
        'status',
        'template_name',
        'template_content',
        'template_character_count',
        'selected_receivers_count',
        'recipient_count',
        'sms_parts_count',
        'encoding',
        'sms_type',
        'min_parts_per_message',
        'max_parts_per_message',
        'avg_parts_per_message',
        'scheduled_at',
        'meta',
    ];

    protected $casts = [
        'selected_receivers_count' => 'integer',
        'template_character_count' => 'integer',
        'recipient_count' => 'integer',
        'sms_parts_count' => 'integer',
        'min_parts_per_message' => 'integer',
        'max_parts_per_message' => 'integer',
        'avg_parts_per_message' => 'decimal:1',
        'scheduled_at' => 'datetime',
        'meta' => 'array',
    ];

    public function sms(): HasMany
    {
        return $this->hasMany(Sms::class);
    }

    public function smsGroup(): BelongsTo
    {
        return $this->belongsTo(SmsGroup::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(SmsTemplate::class, 'sms_template_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }
}
