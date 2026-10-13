<?php

namespace Database\Seeders;

use App\Enums\TransactionTypeEnum;
use App\Models\Group;
use App\Models\RefundPayment;
use App\Models\Student;
use App\Models\Transaction;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FixRefundPaymentWithGroup extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::beginTransaction();
        try {
            $refunds = RefundPayment::whereNotNull('student_group_payment_id')->get();
            foreach ($refunds as $refund) {
                $groupAccount = Group::where('id', $refund->studentGroupPayment->group_id)->withTrashed()->first()?->account;
                $studentAccount = Student::where('id', $refund->studentGroupPayment->student_id)->withTrashed()->first()?->account;
                Transaction::create([
                    'type' => TransactionTypeEnum::TRANSFER->value,
                    'from_account_id' => $groupAccount->id,
                    'to_account_id' => $studentAccount->id,
                    'amount' => $refund->amount,
                    'created_by' => $refund->created_by,
                ]);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        DB::commit();
    }
}
