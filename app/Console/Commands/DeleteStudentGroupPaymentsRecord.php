<?php

namespace App\Console\Commands;

use App\Models\StudentGroupPayment;
use Illuminate\Console\Command;

class DeleteStudentGroupPaymentsRecord extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:delete-student-group-payments-record';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete student group payments records';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $student_group_payments = StudentGroupPayment::all();

        $count = 0;

        foreach ($student_group_payments as $student_group_payment) {
            $count++;
            $student_group_payment->delete();
        }

        $this->info('Removed ' . $count . ' student group payments');
    }
}
