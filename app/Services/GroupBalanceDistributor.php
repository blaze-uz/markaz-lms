<?php

namespace App\Services;

use App\Enums\AccountEnum;
use App\Enums\StaffPaymentTypeEnum;
use App\Enums\TransactionTypeEnum;
use App\Models\Account;
use App\Models\Group;
use App\Models\StaffPayment;
use App\Models\StaffSalary;
use App\Models\Transaction;
use Carbon\Carbon;

class GroupBalanceDistributor
{
    /**
     * Distribute a group's undistributed account balance to the teacher and the
     * main/branch account, creating the matching staff payment and salary document.
     *
     * Works for both active and soft-deleted groups. When a group is soft-deleted
     * its account is soft-deleted too, and the Transaction balance hook can no longer
     * find that account (SoftDeletes global scope on `Account::find()`), so the group
     * account balance is forced to zero explicitly after the transfers to keep the
     * ledger consistent and to prevent the same balance from being distributed twice.
     *
     * @return array{distributed: bool, group_id: int, teacher_id: int|null, amount: int, teacher_amount: int, main_amount: int}|null
     */
    public function distribute(Group $group, int $causerId, ?Carbon $salaryDate = null): ?array
    {
        $groupAccount = Account::withTrashed()->find($group->account_id);
        if (! $groupAccount) {
            return null;
        }

        $amount = (int) $groupAccount->balance;
        if ($amount <= 0) {
            return null;
        }

        $mainAccount = $this->resolveMainAccount($group);
        $teacher = $group->teacher;
        $teacherAccount = $teacher?->account;

        $teacherPercentage = $group->teacher_percentage ?? $teacher?->percentage ?? 0;
        $mainAmount = (int) ($amount * (100 - $teacherPercentage) / 100);
        $teacherAmount = $amount - $mainAmount;

        // If the teacher (or their account) is missing we cannot pay their share,
        // so the whole amount goes to the main account instead of being stranded.
        if (! $teacherAccount) {
            $mainAmount = $amount;
            $teacherAmount = 0;
        }

        $transactionIds = [];
        $teacherTransactionId = null;

        if ($mainAmount > 0 && $mainAccount) {
            $mainTransaction = Transaction::create([
                'description' => 'Guruh balansi taqsimoti (asosiy hisob)',
                'from_account_id' => $groupAccount->id,
                'to_account_id' => $mainAccount->id,
                'amount' => $mainAmount,
                'type' => TransactionTypeEnum::TRANSFER->value,
                'created_by' => $causerId,
            ]);
            $transactionIds[] = $mainTransaction->id;
        }

        if ($teacherAmount > 0 && $teacherAccount) {
            $teacherTransaction = Transaction::create([
                'description' => "Guruh balansi taqsimoti (o'qituvchi)",
                'from_account_id' => $groupAccount->id,
                'to_account_id' => $teacherAccount->id,
                'amount' => $teacherAmount,
                'type' => TransactionTypeEnum::TRANSFER->value,
                'created_by' => $causerId,
            ]);
            $transactionIds[] = $teacherTransaction->id;
            $teacherTransactionId = $teacherTransaction->id;
        }

        // The Transaction hook only decrements live accounts. For a soft-deleted group
        // account it is a no-op, so force the balance to zero (total distributed always
        // equals the original balance, so this is idempotent for live accounts too).
        $groupAccount->balance = 0;
        $groupAccount->save();

        if ($teacher) {
            $staffPayment = StaffPayment::create([
                'user_id' => $teacher->id,
                'type' => StaffPaymentTypeEnum::GROUP->value,
                'payment_type' => 'group',
                'amount' => $teacherAmount,
                'created_by' => $causerId,
            ]);
            $staffPayment->transactions()->attach($transactionIds);

            if ($teacherTransactionId) {
                $this->staffSalaryDocument($teacher->id, [$teacherTransactionId], $causerId, $salaryDate);
            }
        }

        return [
            'distributed' => true,
            'group_id' => $group->id,
            'teacher_id' => $teacher?->id,
            'amount' => $amount,
            'teacher_amount' => $teacherAmount,
            'main_amount' => $mainAmount,
        ];
    }

    private function resolveMainAccount(Group $group): ?Account
    {
        $branchAccountId = $group->branches()->value('account_id');
        $branchAccount = $branchAccountId ? Account::find($branchAccountId) : null;

        return $branchAccount ?: Account::where('account_code', AccountEnum::MAIN->value)->first();
    }

    /**
     * @param  array<int>  $transactionIds
     */
    private function staffSalaryDocument(int $staffId, array $transactionIds, int $causerId, ?Carbon $salaryDate = null): void
    {
        $date = $salaryDate ?? now();
        $month = $date->format('Y-m');

        $record = StaffSalary::where('staff_id', $staffId)
            ->where('date', 'LIKE', $month.'%')
            ->first();

        if (! $record) {
            $record = StaffSalary::create([
                'staff_id' => $staffId,
                'date' => $date,
                'causer_id' => $causerId,
            ]);
        }

        $amounts = Transaction::whereIn('id', $transactionIds)->pluck('amount', 'id');

        $attach = [];
        foreach ($transactionIds as $id) {
            $attach[$id] = [
                'type' => 'income',
                'amount' => $amounts->get($id),
                'causer_id' => $causerId,
            ];
        }

        $record->transactions()->syncWithoutDetaching($attach);
    }
}
