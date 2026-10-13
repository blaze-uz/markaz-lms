<?php

namespace App\Models;

use App\Traits\HasBranch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Lead extends Model
{
    use HasBranch, HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'name',
        'phone',
        'phone2',
        'address',
        'comment',
        'collection_id',
        'board_id',
        'order',
        'student_id',
        'status',
        'chosen_days',
        'chosen_time',
        'causer_id',
        'course_id',
        'teacher_id',
        'session_id',
        'attend',
        'custom_attributes',
    ];

    protected $casts = [
        'custom_attributes' => 'array',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'phone', 'phone2', 'address', 'comment', 'collection_id', 'board_id', 'order', 'status', 'chosen_days', 'chosen_time', 'course_id', 'teacher_id', 'session_id', 'attend', 'custom_attributes'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function getLogNameToUse(): string
    {
        return 'lead';
    }

    public function calls(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(LeadCall::class);
    }

    public function comments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(LeadComment::class);
    }

    public function collection(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(LeadCollection::class, 'collection_id');
    }

    public function board(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(LeadBoard::class, 'board_id');
    }

    public function causer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'causer_id');
    }

    public function course(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function teacher(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function student(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function reminders(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(LeadReminder::class);
    }
}
