<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DeleteTransactionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:delete-transactions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete all transaction data while keeping account data intact';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to delete transaction data...');

        // Delete expense transactions
        DB::table('expense_transactions')->delete();
        $this->info('Deleted expense transactions');

        // Delete staff salary transactions
        DB::table('staff_salary_transactions')->delete();
        $this->info('Deleted staff salary transactions');

        // Delete staff payment transactions
        DB::table('staff_payment_transaction')->delete();
        $this->info('Deleted staff payment transactions');

        // Delete main transactions
        DB::table('transactions')->delete();
        $this->info('Deleted main transactions');

        // Reset all account balances to 0
        DB::table('accounts')->update(['balance' => 0]);
        $this->info('Reset all account balances to 0');

        $this->info('Successfully deleted all transaction data while preserving account data.');
    }
}
