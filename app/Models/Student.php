<?php

namespace App\Models;

use App\Enums\StudentGender;
use App\Traits\HasBranch;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends BaseEntity
{
    use HasBranch;

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::creating(function ($student) {
            $student->created_by_id = auth()->id();
        });

        // Clear caches when students are modified
        static::created(function ($student) {
            // Force immediate cache clearing
            if (isset($student->branches) && $student->branches->isNotEmpty()) {
                foreach ($student->branches as $branch) {
                    \App\Services\StudentCacheService::clearCache($branch->id);
                }
            } else {
                // If branches are not loaded, clear cache for current branch
                $currentBranchId = auth()->check() ? auth()->user()->current_branch_id : null;
                if ($currentBranchId) {
                    \App\Services\StudentCacheService::clearCache($currentBranchId);
                }
            }
        });

        static::updated(function ($student) {
            // For updated students, ensure we clear cache for all relevant branches
            $currentBranchId = auth()->check() ? auth()->user()->current_branch_id : null;
            if ($currentBranchId) {
                \App\Services\StudentCacheService::clearCache($currentBranchId);
            }

            if (isset($student->branches) && $student->branches->isNotEmpty()) {
                foreach ($student->branches as $branch) {
                    \App\Services\StudentCacheService::clearCache($branch->id);
                }
            }
        });

        static::deleted(function ($student) {
            foreach ($student->branches as $branch) {
                \App\Services\StudentCacheService::clearCache($branch->id);
            }
        });

        static::restored(function ($student) {
            foreach ($student->branches as $branch) {
                \App\Services\StudentCacheService::clearCache($branch->id);
            }
        });
    }

    use SoftDeletes;

    protected $fillable = [
        'name',
        'surname',
        'phone',
        'parent_phone',
        'gender',
        'birthday',
        'comment',
        'balance',
        'phone2',
        'address',
        'lead_id',
        'account_id',
        'created_by_id',
        'telegram_chat_id',
    ];

    protected $casts = [
        'gender' => StudentGender::class,
    ];

    protected $appends = [
        'balance',
    ];

    public function courses(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'course_student');
    }

    public function teachers(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'student_teacher');
    }

    public function groups(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'group_student')
            ->where('state', '!=', 'completed')
            ->withPivot('attached_at')
            ->withPivot('amount')
            ->withPivot('comment')
            ->withTimestamps();
    }

    public function completedGroups(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'group_student')
            ->where('state', 'completed')
            ->withPivot('attached_at')
            ->withPivot('amount')
            ->withPivot('comment')
            ->withTimestamps();
    }

    public function attendances(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function getFormattedPhone($parent_phone = null)
    {
        $phone = $parent_phone ? $this->parent_phone : $this->phone;
        if (strlen($phone) == 12) {
            return '+'.substr($phone, 0, 3).'('.substr($phone, 3, 2).')'.substr($phone, 5, 3).'-'.substr($phone, 8, 2).'-'.substr($phone, 10, 2);
        } elseif (strlen($phone) == 9) {
            return '+998('.substr($phone, 0, 2).')'.substr($phone, 2, 3).'-'.substr($phone, 5, 2).'-'.substr($phone, 7, 2);
        }

        return $phone;
    }

    public function getFormattedPhone2()
    {
        if (strlen($this->phone2) == 12) {
            return '+'.substr($this->phone2, 0, 3).'('.substr($this->phone2, 3, 2).')'.substr($this->phone2, 5, 3).'-'.substr($this->phone2, 8, 2).'-'.substr($this->phone2, 10, 2);
        } elseif (strlen($this->phone2) == 9) {
            return '+998('.substr($this->phone2, 0, 2).')'.substr($this->phone2, 2, 3).'-'.substr($this->phone2, 5, 2).'-'.substr($this->phone2, 7, 2);
        }

        return $this->phone2;
    }

    public function getFullName(): string
    {
        return $this->name.' '.$this->surname;
    }

    public function groupPayments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(StudentGroupPayment::class);
    }

    public function lead(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(KanbanTask::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    //morph sms
    public function sms()
    {
        return $this->morphMany(Sms::class, 'smsable');
    }

    public function calls()
    {
        return $this->hasMany(Call::class);
    }

    public function created_by()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function deleteInfo(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(StudentDeleteInfo::class);
    }

    public function reminders(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(StudentReminder::class);
    }

    public function getBalanceAttribute()
    {
        $query = $this->groupPayments()->where('left_amount', '>', 0);

        if (auth()->check() && auth()->user()->hasRole('Teacher')) {
            $query->whereIn('group_id', auth()->user()->groups()->pluck('id'));
        }

        $leftAmount = $query->sum('left_amount');

        return $this->account?->balance - $leftAmount ?? 0;
    }

    public function getAge()
    {
        return $this->birthday ? now()->diffInYears($this->birthday) : null;
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    // Add a method to safely attach a branch

    /**
     * Safely attach a branch to the student (prevents duplicates)
     *
     * @param  int  $branchId
     * @return void
     */
    public function safelyAttachBranch($branchId)
    {
        // Check if already attached
        if (! $this->branches()->where('branch_id', $branchId)->exists()) {
            $this->branches()->attach($branchId);
        }
    }
}
