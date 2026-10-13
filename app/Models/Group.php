<?php

namespace App\Models;

use App\Traits\HasBranch;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache; // Add this import

class Group extends BaseEntity
{
    use HasBranch, SoftDeletes;

    // state: demo, active, finished
    // payment_type: date_to_date, by_count_lessons, one_time
    protected $fillable = [
        'name',
        'course_id',
        'teacher_id',
        'teacher_percentage',
        'price',
        'payment_type',
        'start_date',
        'lesson_count',
        'duration_months',
        'state',
        'account_id',
        'completed_at',
    ];

    protected $casts = [
        'start_date' => 'datetime',
    ];

    /**
     * Constructor with cache invalidation hooks
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        self::created(function ($model) {
            // Clear group cache
            Cache::forget('groups');

            // Clear branch-specific count caches (if applicable)
            $model->clearGroupCountCaches();
        });

        self::updated(function ($model) {
            // Clear group cache
            Cache::forget('groups');

            $model->clearGroupCountCaches();
        });

        self::deleted(function ($model) {
            // Clear group cache
            Cache::forget('groups');

            $model->clearGroupCountCaches();
        });
    }

    public static function search($search)
    {
        return empty($search) ? static::query()
            : static::query()->where('name', 'ilike', "%{$search}%")
                ->orWhereHas('course', function ($query) use ($search) {
                    $query->where('name', 'ilike', "%{$search}%");
                })
                ->orWhereHas('teacher', function ($query) use ($search) {
                    $query->where('name', 'ilike', "%{$search}%");
                });
    }

    public function teacher(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function course(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function groupTimes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(GroupTime::class);
    }

    public function students(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'group_student')
            ->withPivot('attached_at', 'amount', 'comment')
            ->withTimestamps();
    }

    //get completed group students
    public function completedStudents(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'group_student')
            ->withPivot('attached_at', 'amount', 'comment')
            ->withTimestamps();
    }

    public function attendances(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function payments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function getPaymentDate(): string
    {
        $range = $this->studentGroupPayments()->orderBy('id', 'desc')->first();
        if ($range) {
            $date = explode('/', $range->range);
            if (is_numeric($date[0])) {
                return $range->range;
            } else {
                return debtRange($range->range);
            }
        } else {
            return 'N/A';
        }
    }

    public function studentGroupPayments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(StudentGroupPayment::class);
    }

    public function account(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function getCurrentLesson()
    {
        //get current lesson by attendance
        return Attendance::where('group_id', $this->id)
            ->distinct()
            ->select('date')
            ->get()
            ->count();
    }

    public function scopeCompleted($query)
    {
        return $query->where('state', 'completed');
    }

    public function scopeActive($query)
    {
        return $query->where('state', 'active');
    }

    public function scopeDemo($query)
    {
        return $query->where('state', 'demo');
    }

    public function scopeNotCompleted($query)
    {
        return $query->where('state', '!=', 'completed');
    }

    public function getLeftGroupTime($locale = 'uz_Latn')
    {
        $startDate = $this->getStartDate();

        // calculate left group time in days
        return $startDate->copy()->addMonths($this->getCourseDuration())->locale($locale);
    }

    public function isGroupExpired()
    {
        $endDate = $this->getEndDate();

        // check if the current date is after the end date
        return Carbon::now()->gt($endDate);
    }

    protected function getStartDate()
    {
        return Carbon::parse($this->start_date);
    }

    public function getEndDate()
    {
        return $this->getStartDate()->copy()->addMonths($this->getCourseDuration());
    }

    protected function getCourseDuration()
    {
        return $this->course->duration ?? 0;
    }

    public function getHasAttendanceAttribute(): bool
    {
        return $this->attendances()->whereDate('date', today())->exists();
    }

    public function boards(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(KanbanBoard::class);
    }

    public function attendanceSettings(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(GroupAttendanceSettings::class);
    }

    public function hasOddDays(): bool
    {
        // Check if the group has group times on Monday, Wednesday, or Friday
        return $this->groupTimes()->whereIn('name', ['monday', 'wednesday', 'friday'])->exists();
    }

    public function hasEvenDays(): bool
    {
        // Check if the group has group times on Tuesday, Thursday, or Saturday
        return $this->groupTimes()->whereIn('name', ['tuesday', 'thursday', 'saturday'])->exists();
    }

    public function hasBothOddAndEvenDays(): bool
    {
        // Check if the group has both odd and even days
        return $this->hasOddDays() && $this->hasEvenDays();
    }

    public function hasSunday(): bool
    {
        // Check if the group has group times on Sunday
        return $this->groupTimes()->where('name', 'sunday')->exists();
    }

    protected function clearGroupCountCaches()
    {
        // Clear branch-specific count caches (if applicable)
        foreach (Branch::pluck('id') as $branchId) {
            Cache::forget("groups_count_{$branchId}");
        }

        // Clear dashboard statistics cache that might include course counts
        Cache::forget('dashboard_statistics');
    }

    /**
     * Get the students count attribute with fallback to relationship count
     */
    public function getStudentsCountAttribute($value)
    {
        // If withCount was used, return the cached count
        if ($value !== null) {
            return $value;
        }

        // Fallback to counting the relationship
        return $this->students()->count();
    }
}
