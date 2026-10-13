<?php

namespace App\Models;

use App\Traits\HasBranch;
use Illuminate\Support\Facades\Cache;

class Course extends BaseEntity
{
    use HasBranch;

    protected $fillable = [
        'name',
        'price',
        'duration',
        'description',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        self::created(function ($model) {
            // Clear standard course cache
            Cache::forget('courses');

            // Clear course count caches that might be used in statistics
            $this->clearCourseCountCaches();
        });

        self::updated(function ($model) {
            // Clear standard course cache
            Cache::forget('courses');

            // If status might have changed, we should also clear count caches
            if ($model->isDirty('status')) {
                $this->clearCourseCountCaches();
            }
        });

        self::deleted(function ($model) {
            // Clear standard course cache
            Cache::forget('courses');

            // Clear course count caches
            $this->clearCourseCountCaches();
        });
    }

    /**
     * Clear all course count related caches
     */
    protected function clearCourseCountCaches()
    {
        // Clear branch-specific count caches (if applicable)
        foreach (Branch::pluck('id') as $branchId) {
            Cache::forget("courses_count_{$branchId}");
        }

        // Clear dashboard statistics cache that might include course counts
        Cache::forget('dashboard_statistics');
    }

    public function groups(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Group::class);
    }

    public function kanbanTasks(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(KanbanTask::class);
    }
}
