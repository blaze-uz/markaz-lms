<?php

namespace App\Console\Commands;

use App\Enums\AccountEnum;
use App\Models\Account;
use App\Models\Attendance;
use App\Models\Payment;
use App\Models\Student;
use App\Models\StudentDeleteInfo;
use App\Models\StudentGroupPayment;
use App\Models\Transaction;
use Illuminate\Console\Command;

class TruncateStudents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:truncate-students';

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
        StudentGroupPayment::truncate();
        Transaction::truncate();
        Attendance::truncate();
        StudentDeleteInfo::truncate();
        Payment::truncate();
        Student::truncate();
        Account::where('account_code', AccountEnum::STUDENT->value)->delete();
        $this->info('Students truncated :)');
    }
}
