<?php

namespace App\Observers;

use App\Models\Student;
use App\Services\StudentCacheService;

class StudentObserver
{
    /**
     * Handle the Student "created" event.
     */
    public function created(Student $student): void
    {
        StudentCacheService::invalidateForStudent($student);
    }

    /**
     * Handle the Student "deleted" event.
     */
    public function deleted(Student $student): void
    {
        StudentCacheService::invalidateForStudent($student);
    }

    /**
     * Handle the Student "restored" event.
     */
    public function restored(Student $student): void
    {
        StudentCacheService::invalidateForStudent($student);
    }

    /**
     * Handle the Student "force deleted" event.
     */
    public function forceDeleted(Student $student): void
    {
        StudentCacheService::invalidateForStudent($student);
    }
}
