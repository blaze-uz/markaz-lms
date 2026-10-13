<?php

namespace App\Jobs;

use App\Enums\AccountEnum;
use App\Enums\StaffPaymentTypeEnum;
use App\Enums\TransactionTypeEnum;
use App\Models\Account;
use App\Models\Group;
use App\Models\Settings;
use App\Models\StaffPayment;
use App\Models\StaffSalary;
use App\Models\Transaction;

class TransferGroupAmount extends BaseJob
{
    /**
     * Create a new job instance.
     */
    public function __construct(
        public $group_id,
        public $causer_id
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $canTransfer = $this->canTransfer();
        if (! $canTransfer) {
            info('Auto payment for staff is disabled');

            return;
        }

        $group = Group::with(['account', 'branches', 'teacher'])->find($this->group_id);
        $teacher = $group->teacher;
        $group_account = $group->account;
        $teacher_account = $group->teacher->account;
        $transactions = [];
        $staffPaymentTransactions = [];

        $group_branch_account = Account::find($group->branches()->value('account_id'));
        if (! $group_branch_account) {
            $main_account = Account::where('account_code', AccountEnum::MAIN->value)->first();
        } else {
            $main_account = $group_branch_account;
        }

        $teacher_percentage = $group->teacher_percentage ?? $teacher->percentage;
        $group_amount = $group->account->getBalance();
        $main_amount = intval($group_amount * (100 - $teacher_percentage) / 100);
        $teacher_amount = $group_amount - $main_amount;
        if ($main_amount > 0) {
            $main_transaction = Transaction::create([
                'from_account_id' => $group_account->id,
                'to_account_id' => $main_account->id,
                'amount' => $main_amount,
                'type' => TransactionTypeEnum::TRANSFER->value,
                'created_by' => $this->causer_id,
            ]);
            $transactions[] = $main_transaction->id;
        }
        if ($teacher_amount > 0) {
            $teacher_transaction = Transaction::create([
                'from_account_id' => $group_account->id,
                'to_account_id' => $teacher_account->id,
                'amount' => $teacher_amount,
                'type' => TransactionTypeEnum::TRANSFER->value,
                'created_by' => $this->causer_id,
            ]);
            $transactions[] = $teacher_transaction->id;
            $staffPaymentTransactions[] = $teacher_transaction->id;
        }

        $staffPayment = StaffPayment::create([
            'user_id' => $teacher->id,
            'type' => StaffPaymentTypeEnum::GROUP->value,
            'payment_type' => 'group',
            'amount' => $teacher_amount,
            'created_by' => $this->causer_id,
        ]);

        $staffPayment->transactions()->attach($transactions);
        $this->staffSalaryDocument($teacher->id, $staffPaymentTransactions);
    }

    private function staffSalaryDocument($staffId, $staffPaymentTransactions, $type = 'income'): void
    {
        $currentMonth = now()->format('Y-m');

        $staffSalaryRecord = StaffSalary::where('staff_id', $staffId)
            ->where('date', 'LIKE', $currentMonth.'%')
            ->first();

        if (! $staffSalaryRecord) {
            $staffSalaryRecord = StaffSalary::create([
                'staff_id' => $staffId,
                'date' => now(),
                'causer_id' => $this->causer_id,
            ]);
        }

        $transactions = Transaction::whereIn('id', $staffPaymentTransactions)->pluck('amount', 'id');

        $attachData = [];
        foreach ($staffPaymentTransactions as $transactionId) {
            $attachData[$transactionId] = [
                'type' => $type,
                'amount' => $transactions->get($transactionId),
                'causer_id' => $this->causer_id,
            ];
        }

        $staffSalaryRecord->transactions()->syncWithoutDetaching($attachData);
    }

    public function canTransfer()
    {
        $auto_payment_setting = Settings::where('key', 'auto_payment_for_staff')->first();
        if ($auto_payment_setting && $auto_payment_setting->value === 'enable') {
            return true;
        }

        return false;
    }
}
