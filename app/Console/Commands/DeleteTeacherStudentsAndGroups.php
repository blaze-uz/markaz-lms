<?php

namespace App\Console\Commands;

use App\Models\RefundPayment;
use Illuminate\Console\Command;

class DeleteTeacherStudentsAndGroups extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:delete-teacher-students-and-groups';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Ask for teacher id
        $teacher_id = $this->ask('Enter teacher id');

        // Fetch groups for the given teacher
        $groups = \App\Models\Group::where('teacher_id', $teacher_id)->withTrashed()->get();

        // Fetch students associated with the groups of the given teacher
        $students = \App\Models\Student::whereHas('groups', function ($query) use ($teacher_id) {
            $query->where('teacher_id', $teacher_id);
        })->get();

        // Create progress bar
        $bar = $this->output->createProgressBar($groups->count() + $students->count());

        foreach ($groups as $group) {
            // Delete attendances
            $group->attendances()->withTrashed()->forceDelete();

            // Delete group payments and associated refunds
            $group->studentGroupPayments()->withTrashed()->get()->each(function ($payment) {
                // Delete associated refund payments first
                $payment->refundPayments()->each(function ($refund) {
                    $refund->forceDelete(); // Delete each refund record
                });

                // Then, delete the payment itself
                $payment->forceDelete();
            });

            // Finally, delete the group
            $group->forceDelete();
            $bar->advance();
        }

        // Delete students associated with the teacher's groups
        foreach ($students as $student) {
            // Delete additional student-related records
            $student->deleteInfo()->delete();
            $student->attendances()->withTrashed()->forceDelete();
            $student->groupPayments()->withTrashed()->forceDelete();

            // Delete student account if exists
            if ($student->account) {
                $student->account->delete();
            }

            // Force delete the student record
            $student->forceDelete();

            // Clear student card stats cache
            cache()->forget('student_card_stats');
            $bar->advance();
        }

        $bar->finish();
        $this->info('Students and groups deleted successfully');
    }
}
