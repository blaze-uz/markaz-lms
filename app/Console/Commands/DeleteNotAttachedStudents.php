<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use App\Models\Student;
use Illuminate\Console\Command;

class DeleteNotAttachedStudents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:delete-not-attached-students';

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
        $students = Student::doesntHave('groups')->get();
        $deleted = Student::onlyTrashed()->get();

        $this->deleteStudents($students);
        $this->deleteStudents($deleted);

        $this->info('Students deleted successfully');
    }

    private function deleteStudents($students)
    {
        foreach ($students as $item) {
            $item->deleteInfo()->delete();
            $item->attendances()->withTrashed()->forceDelete();
            $item->groupPayments()->withTrashed()->forceDelete();
            if ($item->account) {
                $item->account->delete();
            }
            $item->forceDelete();
            cache()->forget('student_card_stats');
        }
    }
}
