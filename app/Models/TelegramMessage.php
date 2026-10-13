<?php

namespace App\Models;

use App\Traits\HasBranch;
use App\Traits\UsesDynamicConnection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TelegramMessage extends Model
{
    use HasBranch, SoftDeletes, UsesDynamicConnection;

    protected $fillable = [
        'student_id',
        'message',
        'status',
        'chat_id',
        'telegram_bot_id',
        'error_log',
        'sent_at',
        'created_by',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }
}
