<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class Account extends BaseEntity
{
    use SoftDeletes;
    protected $fillable = [
        'name',
        'account_code',
        'balance',
        'status',
    ];

    public static function search(mixed $search)
    {
        return empty($search) ? static::query()
            : static::query()->where('name', 'ilike', '%' . $search . '%');
    }

    public function income_transactions()
    {
        return $this->hasMany(Transaction::class, 'to_account_id');
    }

    public function expense_transactions()
    {
        return $this->hasMany(Transaction::class, 'from_account_id');
    }

    public function getBalance()
    {
        return $this->balance;
    }

    /**
     * This approach for run the refresh account balance command
     *
     * @return int|mixed
     */
    public function getItemBalance()
    {
        $incomeTransactions = $this->income_transactions()->sum('amount');
        $expenseTransactions = $this->expense_transactions()->sum('amount');
        return $incomeTransactions - $expenseTransactions;
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
