<?php

namespace App\Console\Commands;

use App\Models\Group;
use App\Models\StudentGroupPayment;
use Illuminate\Console\Command;

class AddDebtCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:add-debt-command';

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
        $group = $this->ask('Enter group ID');
        $range = $this->ask('Enter range. Example: 1/10 or 2024-10-03/2024-11-03');
        $group = Group::find($group);
        if (!$group) {
            $this->error('Group not found');
            return;
        }
        $this->calculateDebt($group, $range);
        $this->info('Debts added successfully');
    }

    public function calculateDebt($group, $range): void
    {
        $students = $group->students;
        foreach ($students as $student) {
            $has_debt = StudentGroupPayment::where('group_id', $group->id)
                ->where('student_id', $student->id)
                ->where('range', $range)->count();
            if (!$has_debt)
                $this->createDebt($student, $group, $range);
        }
    }

    public function createDebt($student, $group, $range)
    {
        $amount = $student->pivot->amount ?? $group->price;

        return StudentGroupPayment::create([
            'student_id' => $student->id,
            'group_id' => $group->id,
            'date' => today(),
            'amount' => $amount,
            'left_amount' => $amount,
            'range' => $range
        ]);
    }
}
