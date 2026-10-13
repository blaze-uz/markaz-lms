<?php

namespace App\Models;

use App\Enums\AttendanceStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attendance extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'student_id',
        'group_id',
        'status',
        'comment',
        'theme',
        'date',
        'monthly_payment',
        'score',
        'homework',
        'is_notified',
        'notify_time',
    ];

    protected $casts = [
        'status' => AttendanceStatus::class,
        'date' => 'datetime',
        'notify_time' => 'datetime',
        'is_notified' => 'boolean',
        'score' => 'integer',
        'monthly_payment' => 'decimal:2',
        'homework' => 'boolean',
    ];

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
