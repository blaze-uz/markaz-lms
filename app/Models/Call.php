<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Call extends Model
{
    use HasFactory;

    protected $table = 'student_calls';

    protected $fillable = [
        'student_id',
        'user_id',
        'status',
        'description',
        'phone',
        'duration',
        'called_at',
    ];

    protected $casts = [
        'called_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getStatusTextAttribute()
    {
        return match ($this->status) {
            'answered' => __('translation.calls.answered'),
            'not_answered' => __('translation.calls.not_answered'),
            'busy' => __('translation.calls.busy'),
            'no_answer' => __('translation.calls.no_answer'),
            default => $this->status,
        };
    }

    public function getStatusColorAttribute()
    {
        return match ($this->status) {
            'answered' => 'success',
            'not_answered' => 'danger',
            'busy' => 'warning',
            'no_answer' => 'secondary',
            default => 'secondary',
        };
    }

    public function getFormattedDurationAttribute()
    {
        if (!$this->duration) {
            return '-';
        }

        $minutes = floor($this->duration / 60);
        $seconds = $this->duration % 60;

        if ($minutes > 0) {
            return sprintf('%dm %ds', $minutes, $seconds);
        }

        return sprintf('%ds', $seconds);
    }
}
