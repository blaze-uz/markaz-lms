<?php

namespace App\Jobs;

use App\Enums\AccountEnum;
use App\Enums\TransactionTypeEnum;
use App\Models\Account;
use App\Models\StaffSalary;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UserContract extends BaseJob
{
    public $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        info('UserContract');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //get today's date like //21, 22 etc
        $today = date('d');

        //get all users whose payment_day is today
        $user_contracts = \App\Models\UserContract::where('payment_day', $today)->get();

        foreach ($user_contracts as $user_contract) {
            $this->transferAmount($user_contract);
        }
    }

    private function transferAmount(mixed $user_contract): void
    {
        DB::transaction(function () use ($user_contract) {
            //skip if user has already paid
            if ($user_contract->users()->wherePivot('paid_at', now())->exists()) {
                return;
            }

            $user = User::find($user_contract->user_id);

            $main = Account::where('account_code', AccountEnum::MAIN)->first();
            $user_account = $user->account;

            $transaction_id = $this->createTransaction($main, $user_account, $user_contract->amount);
            $user->user_contracts()->attach($user_contract->id, [
                'paid_at' => now(),
                'transaction_id' => $transaction_id,
            ]);

            $this->staffSalaryDocument($user->id, [$transaction_id]);
        });
    }

    private function createTransaction($main, $user_account, $amount)
    {
        $transaction = Transaction::create([
            'amount' => $amount,
            'from_account_id' => $main->id,
            'to_account_id' => $user_account->id,
            'description' => 'User contract payment',
            'type' => TransactionTypeEnum::TRANSFER->value,
        ]);

        return $transaction->id;
    }

    private function staffSalaryDocument($user_id, $staffPaymentTransactions, $type = 'income'): void
    {
        $currentMonth = now()->format('Y-m');

        $staffSalaryRecord = StaffSalary::where('staff_id', $user_id)
            ->where('date', 'LIKE', $currentMonth.'%')
            ->first();

        if (! $staffSalaryRecord) {
            $staffSalaryRecord = StaffSalary::create([
                'staff_id' => $user_id,
                'date' => now(),
            ]);
        }

        $transactions = Transaction::whereIn('id', $staffPaymentTransactions)->pluck('amount', 'id');

        $attachData = [];
        foreach ($staffPaymentTransactions as $transactionId) {
            $attachData[$transactionId] = [
                'type' => $type,
                'amount' => $transactions->get($transactionId),
            ];
        }

        $staffSalaryRecord->transactions()->syncWithoutDetaching($attachData);
    }
}
