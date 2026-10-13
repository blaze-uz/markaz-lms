<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixDublicateAttendanceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fix-dublicate-attendance-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove duplicated attendance records';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Find duplicated attendances based on student_id, group_id, and date
        $duplicates = DB::table('attendances')
            ->select('student_id', 'group_id', 'date', DB::raw('COUNT(*) as total_count'))
            ->groupBy('student_id', 'group_id', 'date')
            ->having(DB::raw('COUNT(*)'), '>', 1)
            ->get();

        foreach ($duplicates as $duplicate) {
            // Get all duplicate records except the first one (keeping one record)
            $duplicateRecords = Attendance::where('student_id', $duplicate->student_id)
                ->where('group_id', $duplicate->group_id)
                ->whereDate('date', $duplicate->date)
                ->orderBy('id')
                ->skip(1)
                ->take($duplicate->total_count - 1)
                ->get();

            foreach ($duplicateRecords as $record) {
                // Soft delete the duplicate records
                $record->delete();
            }
        }

        $this->info('Duplicate attendance records have been removed.');
    }
}
