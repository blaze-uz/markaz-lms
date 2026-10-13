<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentDiscount extends Model
{

    protected $fillable = [
        'payment_id',
        'transaction_id',
        'amount'
    ];

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

}
