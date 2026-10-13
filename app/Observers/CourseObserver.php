<?php

namespace App\Observers;

use App\Models\Course;
use Illuminate\Support\Facades\Cache;

class CourseObserver
{
    /**
     * Handle the Course "created" event.
     */
    public function created(Course $course): void
    {
        Cache::forget('courses_count');
    }

    /**
     * Handle the Course "deleted" event.
     */
    public function deleted(Course $course): void
    {
        Cache::forget('courses_count');
    }

    /**
     * Handle the Course "restored" event.
     */
    public function restored(Course $course): void
    {
        Cache::forget('courses_count');
    }

    /**
     * Handle the Course "force deleted" event.
     */
    public function forceDeleted(Course $course): void
    {
        Cache::forget('courses_count');
    }
}
