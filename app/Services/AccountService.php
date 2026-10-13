<?php

namespace App\Services;

use App\Enums\AccountEnum;
use App\Enums\TransactionTypeEnum;
use App\Models\Account;
use App\Models\Transaction;
use App\Models\Transfer;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AccountService extends BaseService
{
    public function __construct(Account $account)
    {
        $this->model = $account;
    }


    public function find($id, $with = [])
    {
        $account = $this->model::with($with)->findOrFail($id);
        $account->transactions = Transaction::where('to_account_id', $account->id)->orWhere('from_account_id', $account->id)->get();
        return $account;
    }

    public function getAccountsSum($account_code)
    {
        return Account::where('account_code', $account_code)->sum('balance');
    }


    public function getTransactions($id)
    {
        return Transaction::where('to_account_id', $id)->orWhere('from_account_id', $id)->orderByDesc('id')->paginate(20);
    }

    public function transfer(array $data): void
    {
        DB::beginTransaction();
        try {
            $data['amount'] = removeComma($data['amount']);
            $this->createTransfer($data);
            $this->createTransaction($data);
            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
            throw new Exception($exception->getMessage(), 400);
        }
    }

    private function createTransaction(mixed $transfer): void
    {
        Transaction::create([
            'type' => TransactionTypeEnum::TRANSFER->value,
            'from_account_id' => $transfer['from_account_id'],
            'to_account_id' => $transfer['to_account_id'],
            'amount' => $transfer['amount'],
            'created_by' => auth()->id(),
        ]);
    }

    private function createTransfer(mixed $transfer): void
    {
        Transfer::create([
            'income_account_id' => $transfer['to_account_id'],
            'expense_account_id' => $transfer['from_account_id'],
            'amount' => $transfer['amount'],
            'created_by' => auth()->id(),
        ]);
    }

    public function getStaffAccounts()
    {
        return Account::where('account_code', AccountEnum::STAFF->value)->get();
    }

    public function transaction($data, $id): void
    {
        $item = $this->find($id);
        $data['amount'] = removeComma($data['amount']);

        if ($data['type'] == TransactionTypeEnum::INCOME->value) {
            $this->income($data, $item);
        } else {
            $this->expense($data, $item);
        }
    }

    public function income($data, $item): void
    {
        Transaction::create([
            'type' => TransactionTypeEnum::INCOME->value,
            'to_account_id' => $item->id,
            'amount' => $data['amount'],
            'description' => $data['description'] ?? null,
            'created_by' => auth()->id(),
        ]);
    }

    public function expense($data, $item): void
    {
        Transaction::create([
            'type' => TransactionTypeEnum::EXPENSE->value,
            'from_account_id' => $item->id,
            'amount' => $data['amount'],
            'description' => $data['description'] ?? null,
            'created_by' => auth()->id(),
        ]);
    }

    /**
     * Soft delete an account and handle related cleanup
     */
    public function softDeleteAccount($accountId): bool
    {
        $account = Account::findOrFail($accountId);

        // Check if account has any active transactions
        $hasTransactions = Transaction::where('to_account_id', $accountId)
            ->orWhere('from_account_id', $accountId)
            ->exists();

        if ($hasTransactions) {
            throw new \Exception('Cannot delete account with existing transactions. Please transfer or remove transactions first.');
        }

        // Soft delete the account
        $account->delete();

        return true;
    }

    /**
     * Restore a soft deleted account
     */
    public function restoreAccount($accountId): bool
    {
        $account = Account::onlyTrashed()->findOrFail($accountId);
        $account->restore();

        return true;
    }
}
