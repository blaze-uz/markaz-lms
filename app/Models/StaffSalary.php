<?php

namespace App\Models;

class StaffSalary extends BaseEntity
{
    protected $fillable = [
        'staff_id',
        'date',
    ];

    public function staff(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_id', 'id');
    }

    public function transactions(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Transaction::class, 'staff_salary_transactions', 'staff_salary_id', 'transaction_id')
            ->withPivot('type', 'amount', 'causer_id')
            ->using(StaffSalaryTransaction::class)
            ->withTimestamps();
    }

    public function incomeTransactions()
    {
        return $this->transactions()->wherePivot('type', 'income');
    }

    public function expenseTransactions()
    {
        return $this->transactions()->wherePivot('type', 'expense');
    }

    public function getAmountAttribute()
    {
        return $this->transactions()->wherePivot('type', 'income')->sum('transactions.amount');
    }

    public function getGivenAmountAttribute()
    {
        return $this->transactions()->wherePivot('type', 'expense')->sum('transactions.amount');
    }
}