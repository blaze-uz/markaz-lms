<?php

namespace App\Console\Commands;

use App\Enums\AccountEnum;
use App\Enums\TransactionTypeEnum;
use App\Models\Account;
use App\Models\Branch;
use App\Models\Group;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DeleteGroupToStaffTransactionsCommand extends Command
{
    protected $signature = 'app:delete-group-to-staff-transactions
                            {--date= : The date (Y-m-d) for which to delete group-to-staff and group-to-main transactions}';

    protected $description = 'Delete transactions from group accounts to staff and main accounts for a selected date';

    public function handle(): int
    {
        $date = $this->option('date') ?? $this->ask('Enter the date (Y-m-d)');

        if (! $date || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $this->error('Invalid date. Use Y-m-d format (e.g. 2024-01-15).');

            return self::FAILURE;
        }

        $groupAccountIds = Group::pluck('account_id')->filter()->unique()->values()->all();
        $staffAccountIds = User::whereNotNull('account_id')->pluck('account_id')->unique()->values()->all();
        $mainAccountIds = $this->getMainAccountIds();

        $toAccountIds = array_values(array_unique(array_merge($staffAccountIds, $mainAccountIds)));

        if (empty($groupAccountIds) || empty($toAccountIds)) {
            $this->warn('No group or target (staff/main) accounts found.');

            return self::SUCCESS;
        }

        $query = Transaction::query()
            ->where('type', TransactionTypeEnum::TRANSFER)
            ->whereIn('from_account_id', $groupAccountIds)
            ->whereIn('to_account_id', $toAccountIds)
            ->whereDate('created_at', $date);

        $transactions = $query->get();
        $count = $transactions->count();

        if ($count === 0) {
            $this->info("No group-to-staff/main transactions found for {$date}.");

            return self::SUCCESS;
        }

        if (! $this->confirm("Found {$count} transaction(s) for {$date}. Delete them?", true)) {
            return self::SUCCESS;
        }

        DB::transaction(function () use ($transactions): void {
            $transactionIds = $transactions->pluck('id')->all();

            DB::table('staff_salary_transactions')->whereIn('transaction_id', $transactionIds)->delete();
            DB::table('staff_payment_transaction')->whereIn('transaction_id', $transactionIds)->delete();

            foreach ($transactions as $transaction) {
                $transaction->delete();
            }
        });

        $this->info("Deleted {$count} group-to-staff/main transaction(s) for {$date}.");

        return self::SUCCESS;
    }

    /**
     * @return array<int>
     */
    private function getMainAccountIds(): array
    {
        $globalMain = Account::where('account_code', AccountEnum::MAIN->value)->pluck('id');
        $branchMain = Branch::pluck('account_id')->filter()->unique()->values();

        return $globalMain->merge($branchMain)->unique()->values()->all();
    }
}
