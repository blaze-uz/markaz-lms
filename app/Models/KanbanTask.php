<?php

namespace App\Models;

use App\Traits\HasBranch;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class KanbanTask extends BaseEntity
{
    use SoftDeletes, LogsActivity, HasBranch;

    protected $fillable = [
        'name',
        'phone',
        'comment',
        'kanban_board_id',
        'kanban_collection_id',
        'student_id',
        'status',
        'chosen_days',
        'chosen_time',
        'attend',
        'causer_id',
        'course_id',
        'session_id'
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'phone', 'comment', 'kanban_board_id', 'kanban_collection_id', 'student_id', 'status', 'chosen_days', 'chosen_time', 'attend'])
            ->logOnlyDirty();
    }

    public function getLogNameToUse(): string
    {
        return 'kanban_task';
    }

    public function student(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function comments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(KanbanTaskComment::class);
    }

    public function collection(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(KanbanCollection::class, 'kanban_collection_id');
    }

    public function board(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(KanbanBoard::class, 'kanban_board_id');
    }

    public function teachers(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'lead_teacher', 'kanban_task_id', 'teacher_id');
    }

    public function causer()
    {
        return $this->belongsTo(User::class, 'causer_id');
    }

    public function calls(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(KanbanTaskCall::class);
    }

    public static function search($search)
    {
        return empty($search) ? static::query()->with('causer')
            : static::query()->with('causer')
            ->where('name', 'ilike', '%' . $search . '%')
            ->orWhere(DB::raw("replace(replace(replace(phone,'(',''),')',''),'-','')"), 'ilike', '%' . $search . '%');
    }

    public function reminders(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(LeadReminder::class, 'lead_id', 'id');
    }

    public function getReminderAttribute()
    {
        return $this->reminders()
            ->where('reminder_status', 'pending')
            ->whereDate('reminder_date', now()->toDateString())
            ->first();
    }

    public function course(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}
