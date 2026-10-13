<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentReminderRead extends Model
{
    protected $fillable = [
        'student_reminder_id',
        'user_id',
    ];

    public function studentReminder(): BelongsTo
    {
        return $this->belongsTo(StudentReminder::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
