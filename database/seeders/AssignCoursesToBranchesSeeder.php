<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AssignCoursesToBranchesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all branches and courses
        $branches = \App\Models\Branch::all();
        $courses = \App\Models\Course::all();

        if ($branches->isEmpty() || $courses->isEmpty()) {
            $this->command->info('No branches or courses found to assign.');
            return;
        }

        // Assign all courses to all branches (or you can customize this logic)
        foreach ($branches as $branch) {
            foreach ($courses as $course) {
                // Check if the relationship doesn't already exist
                if (!$branch->courses()->where('course_id', $course->id)->exists()) {
                    $branch->courses()->attach($course->id);
                }
            }
        }

        $this->command->info('Successfully assigned courses to branches.');
    }
}
