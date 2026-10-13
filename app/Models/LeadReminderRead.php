<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadReminderRead extends Model
{
    protected $fillable = [
        'lead_reminder_id',
        'user_id',
    ];

    public function leadReminder(): BelongsTo
    {
        return $this->belongsTo(LeadReminder::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
