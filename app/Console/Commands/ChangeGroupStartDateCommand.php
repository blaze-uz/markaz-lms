<?php

namespace App\Console\Commands;

use App\Models\Group;
use App\Models\StudentGroupPayment;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ChangeGroupStartDateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:change-group-start-date-command';

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
        $day = $this->ask('Please provide the day of the month');
        if (!is_numeric($day) || $day < 1 || $day > 31) {
            $this->error('Invalid day. Please provide a number between 1 and 31.');
            return;
        }

        $now = Carbon::now();
        $year = $now->year;
        $month = $now->month;

        // Create a Carbon date object with the correct format
        $currentDate = Carbon::createFromFormat('Y-m-d', $year . '-' . $month . '-' . $day);

        if ($currentDate->month != $month) {
            $this->error('Invalid day. The day exceeds the number of days in the current month.');
            return;
        }

        $studentGroupPayments = StudentGroupPayment::all();
        DB::beginTransaction();
        try {
            $this->updateGroupRangeDate($studentGroupPayments, $currentDate);
            $this->updateGroupStartDate();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('An error occurred while updating the group range date.');
            return;
        }

        $this->info('The group range date has been updated successfully.');
    }

    private function updateGroupRangeDate($studentGroupPayments, $currentDate): void
    {
        foreach ($studentGroupPayments as $studentGroupPayment) {
            $range = explode('/', $studentGroupPayment->range);

            $firstDateOfMonth = $currentDate->firstOfMonth()->format('Y-m-d');
            $lastDateOfMonth = $currentDate->lastOfMonth()->format('Y-m-d');

            // Assign first date of month to $currentDate
            $currentDate = $currentDate->firstOfMonth();

            // New generated range
            $updatedRange = $firstDateOfMonth . '/' . $lastDateOfMonth;

            // Check if the range is valid
            if (!isDate($range[0]) || !isDate($range[1])) {
                continue;
            }

            if (Carbon::parse($range[0])->lessThan($currentDate) && Carbon::parse($range[1])->greaterThan($currentDate)) {
                if ($studentGroupPayment->amount == $studentGroupPayment->left_amount) {
                    $studentGroupPayment->delete();
                } else {
                    $newDate = $currentDate->clone()->subDay();
                    $studentGroupPayment->range = $range[0] . '/' . $newDate->format('Y-m-d');
                    $studentGroupPayment->save();
                }
            } else if (Carbon::parse($range[0])->greaterThan($currentDate) && Carbon::parse($range[1])->greaterThan($currentDate)) {
                $studentGroupPayment->range = $updatedRange;
                $studentGroupPayment->save();
            }

            // Clear the date to first date of month
            $currentDate = $currentDate->firstOfMonth();
        }
    }

    private function updateGroupStartDate()
    {
        $groups = Group::where('payment_type', 'date_to_date')->get();
        foreach ($groups as $group) {
            $group->start_date = Carbon::now()->firstOfMonth()->format('Y-m-d');
            $group->save();
        }
    }
}