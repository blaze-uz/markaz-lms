<?php

namespace App\Services;

use App\Enums\TransactionTypeEnum;
use App\Models\Account;
use App\Models\Expense;
use App\Models\ExpenseItem;
use App\Models\ExpensePlace;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;

class ExpenseService
{
    /**
     * @throws \Exception
     */
    public function store(array $data)
    {
        DB::beginTransaction();
        try {
            $item_id = $data['item_id'];
            $place_id = $data['place_id'] ?? null;

            // Check if item_id is numeric (existing item) or string (new item name)
            $item = is_numeric($item_id) ? ExpenseItem::find((int) $item_id) : null;

            // If item not found, create a new one with the provided name
            if (! $item) {
                $item = ExpenseItem::create([
                    'name' => $item_id,
                ]);
            }

            $place = $place_id ? (is_numeric($place_id) ? ExpensePlace::find((int) $place_id) : null) : null;
            $account = Account::find($data['account_id']);
            $amount = removeComma($data['amount']);
            if (! $account) {
                throw new \Exception('Account not found');
            }
            if ($place_id && ! $place) {
                $place = ExpensePlace::create([
                    'name' => $place_id,
                ]);
            }
            $expense = Expense::create([
                'item_id' => $item->id,
                'quantity' => $data['quantity'],
                'price' => $amount,
                'place_id' => $place?->id,
                'account_id' => $data['account_id'],
                'payment_type_id' => $data['payment_type_id'],
                'created_by' => auth()->id(),
                'date' => $data['date'] ?? now(),
                'description' => $data['description'] ?? 'Xarajat: '.$item->name,
            ]);

            $expense->branches()->attach(auth()->user()->current_branch_id);

            $this->makeTransaction($expense, $account, $amount);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    protected function makeTransaction($item, $account, $amount): void
    {
        $transaction = Transaction::create([
            'type' => TransactionTypeEnum::EXPENSE->value,
            'amount' => $amount,
            'from_account_id' => $account->id,
            'created_by' => auth()->id(),
            'description' => $item->description ?? 'Xarajat: '.$item->item->name,
        ]);

        $item->transaction()->create([
            'transaction_id' => $transaction->id,
            'amount' => $amount,
        ]);
    }

    public function update(Expense $expense, array $data): void
    {
        DB::beginTransaction();
        try {
            $item_id = $data['item_id'];
            $place_id = $data['place_id'] ?? null;

            // Check if item_id is numeric (existing item) or string (new item name)
            $item = is_numeric($item_id) ? ExpenseItem::find((int) $item_id) : null;

            // If item not found, create a new one with the provided name
            if (! $item) {
                $item = ExpenseItem::create([
                    'name' => $item_id,
                ]);
            }

            $place = $place_id ? (is_numeric($place_id) ? ExpensePlace::find((int) $place_id) : null) : null;
            $account = Account::find($data['account_id']);
            $amount = removeComma($data['amount']);
            if (! $account) {
                throw new \Exception('Account not found');
            }
            if ($place_id && ! $place) {
                $place = ExpensePlace::create([
                    'name' => $place_id,
                ]);
            }

            $this->updateTransaction($expense, $account, $amount);
            $expense->update([
                'item_id' => $item->id,
                'quantity' => $data['quantity'],
                'price' => $amount,
                'place_id' => $place?->id,
                'account_id' => $data['account_id'],
                'payment_type_id' => $data['payment_type_id'],
                'created_by' => auth()->id(),
                'description' => $data['description'] ?? 'Xarajat: '.$item->name,
            ]);
            if ($data['date']) {
                $expense->update([
                    'date' => $data['date'],
                ]);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function updateTransaction($item, $account, $amount): void
    {
        //delete old transaction
        $expenseTransaction = $item->transaction;
        //if transaction exists then delete it
        if ($expenseTransaction) {
            if ($expenseTransaction->transaction) {
                $expenseTransaction->transaction->delete();
            }
            $expenseTransaction->delete();
        }

        //create new transaction
        $this->makeTransaction($item, $account, $amount);
    }

    public function destroy(Expense $expense): void
    {
        DB::beginTransaction();
        try {
            $expenseTransaction = $expense->transaction;
            //if transaction exists then delete it
            if ($expenseTransaction) {
                if ($expenseTransaction->transaction) {
                    $expenseTransaction->transaction->delete();
                }
                $expenseTransaction->delete();
            }
            $expense->delete();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function show($id)
    {
        return Expense::with('item', 'place', 'account', 'createdBy')->findOrFail($id);
    }

    public function getActivities($id)
    {
        return Activity::where('subject_id', $id)
            ->where('log_name', 'expenses')
            ->latest('id')
            ->paginate(10);
    }
}
