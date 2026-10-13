<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class StaffSalaryTransaction extends Pivot
{
    protected $fillable = [
        'staff_salary_id',
        'transaction_id',
        'type',
        'amount',
        'causer_id'
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'transaction_id');
    }

    public function causer()
    {
        return $this->belongsTo(User::class, 'causer_id');
    }
}