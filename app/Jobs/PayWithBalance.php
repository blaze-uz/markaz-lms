<?php

namespace App\Jobs;

use App\Enums\TransactionTypeEnum;
use App\Models\Student;
use App\Models\StudentGroupPayment;

class PayWithBalance extends BaseJob
{
    /**
     * Create a new job instance.
     */
    public function __construct(
        private $student_ids = null,
        private $students = null
    ) {
        info('PayWithBalance');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        info('dispaching event');
        if ($this->student_ids == null) {
            $this->students = Student::all();
        } else {
            $this->students = Student::whereIn('id', $this->student_ids)->get();
        }
        info(count($this->students));
        foreach ($this->students as $student) {
            $this->payWithBalance($student);
        }
    }

    public function payWithBalance($student): void
    {
        $studentGroupPayments = StudentGroupPayment::with(['group' => function ($query) {
            $query->withTrashed();
        }])
            ->where('student_id', $student->id)
            ->where('left_amount', '>', 0)
            ->orderBy('created_at', 'asc') // FIFO order
            ->get();

        if ($studentGroupPayments->isEmpty()) {
            return;
        }

        $student_account = $student->account;
        $remaining_balance = $student_account->getBalance();

        if ($remaining_balance <= 0) {
            return;
        }

        foreach ($studentGroupPayments as $debt) {
            if ($remaining_balance <= 0) {
                break;
            }

            $payment_amount = min($remaining_balance, $debt->left_amount);

            if ($payment_amount > 0) {
                $debt->transactions()->create([
                    'amount' => $payment_amount,
                    'from_account_id' => $student->account->id,
                    'to_account_id' => $debt->group->account_id,
                    'description' => 'Pay with balance (FIFO)',
                    'type' => TransactionTypeEnum::TRANSFER->value,
                ]);

                $debt->left_amount -= $payment_amount;
                $debt->update();

                $remaining_balance -= $payment_amount;
            }
        }
    }
}
