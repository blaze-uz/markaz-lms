<?php

namespace App\Console\Commands;

use App\Models\Student;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixDublicateStudentBranches extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fix-dublicate-student-branches';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove duplicate student-branch relationships';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to fix duplicate student-branch relationships...');

        // Get all students with duplicate branch relationships
        $duplicates = DB::table('branch_student')
            ->select('student_id', 'branch_id', DB::raw('COUNT(*) as duplicate_count'))
            ->groupBy('student_id', 'branch_id')
            ->having(DB::raw('COUNT(*)'), '>', 1)
            ->get();

        $this->info("Found {$duplicates->count()} duplicate relationships.");

        $totalRemoved = 0;

        // Process each duplicate
        foreach ($duplicates as $duplicate) {
            // Get all records for this student-branch pair
            $records = DB::table('branch_student')
                ->where('student_id', $duplicate->student_id)
                ->where('branch_id', $duplicate->branch_id)
                ->orderBy('created_at')
                ->get();

            // Keep the oldest record, delete the rest
            $keepId = $records->first()->id;

            $deleted = DB::table('branch_student')
                ->where('student_id', $duplicate->student_id)
                ->where('branch_id', $duplicate->branch_id)
                ->where('id', '!=', $keepId)
                ->delete();

            $totalRemoved += $deleted;

            $this->info("Processed student ID {$duplicate->student_id} with branch ID {$duplicate->branch_id}: removed {$deleted} duplicates.");
        }

        $this->info("Completed! Removed a total of {$totalRemoved} duplicate relationships.");

        // Check for students without any branch
        // $studentsWithoutBranch = Student::whereDoesntHave('branches')
        //     ->with('created_by')
        //     ->get();

        // if ($studentsWithoutBranch->count() > 0) {
        //     $this->warn("Found {$studentsWithoutBranch->count()} students without any branch. You may want to assign them to a branch.");

        //     if ($this->confirm('Do you want to assign these students to their creator\'s current branch?')) {
        //         $assigned = 0;

        //         foreach ($studentsWithoutBranch as $student) {
        //             if ($student->created_by && $student->created_by->current_branch_id) {
        //                 DB::table('branch_student')->insert([
        //                     'student_id' => $student->id,
        //                     'branch_id' => $student->created_by->current_branch_id,
        //                     'created_at' => now(),
        //                     'updated_at' => now(),
        //                 ]);
        //                 $assigned++;
        //             }
        //         }

        //         $this->info("Assigned {$assigned} students to their creator's current branch.");
        //     }
        // }

        return Command::SUCCESS;
    }
}
