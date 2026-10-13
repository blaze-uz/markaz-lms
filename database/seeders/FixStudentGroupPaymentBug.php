<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class FixStudentGroupPaymentBug extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $studentGroupPayments = \App\Models\StudentGroupPayment::all();

        foreach ($studentGroupPayments as $studentGroupPayment) {
            $debt = explode('/', $studentGroupPayment->range);
            //checking $debt[0] int or not
            if (is_numeric($debt[0])) {
                $this->lessonByCount($studentGroupPayment);
            } else {
                $start = dateFormat($debt[0], 'Y-m-d');
                if ($start > today()) {
                    //delete this debt's transactions
                    $transactions = $studentGroupPayment->transactions;
                    foreach ($transactions as $transaction) {
                        $transaction->delete();
                    }
                    //delete this debt
                    $studentGroupPayment->delete();
                }
            }
        }
    }

    private function lessonByCount(mixed $studentGroupPayment): void
    {
        $group = $studentGroupPayment->group;
        $range = $studentGroupPayment->range; //debt range. ex: 1/4
        $debt = explode('/', $range);
        $startLesson = $debt[0];
        $currentLesson = $group->getCurrentLesson();
        if ($currentLesson < $startLesson) {
            //delete this debt's transactions
            $transactions = $studentGroupPayment->transactions;
            foreach ($transactions as $transaction) {
                $transaction->delete();
            }
            //delete this debt
            $studentGroupPayment->delete();
        }
    }
}
