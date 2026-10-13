<?php

namespace App\Console\Commands;

use App\Models\Student;
use Illuminate\Console\Command;

class FixStudentPhoneNumberCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fix-student-phone-number-command';

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
        // Get all students with a phone number length of 12
        $students = Student::whereRaw('LENGTH(phone) = 12')->withTrashed()->get();

        // Create a progress bar
        $bar = $this->output->createProgressBar($students->count());

        // Start the progress bar
        $bar->start();

        foreach ($students as $student) {
            // Modify the phone number
            $student->phone = substr($student->phone, 3);
            $student->save();

            // Advance the progress bar
            $bar->advance();
        }

        // Finish the progress bar
        $bar->finish();

        $this->info(PHP_EOL . 'Phone numbers have been fixed.');
    }
}
