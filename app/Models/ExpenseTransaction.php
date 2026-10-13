<?php

namespace App\Models;

class ExpenseTransaction extends BaseEntity
{
    protected $fillable = [
        'expense_id',
        'transaction_id',
        'amount',
    ];

    public function expense(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Expense::class);
    }

    public function transaction(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }
}
