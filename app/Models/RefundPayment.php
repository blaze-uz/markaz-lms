<?php

namespace App\Models;

use App\Traits\HasBranch;

class RefundPayment extends BaseEntity
{
    use HasBranch;

    protected $fillable = [
        'payment_id', // this is the payment id that is being refunded
        'transaction_id',
        'amount',
        'student_group_payment_id',
        'payment_type_id',
        'created_by',
        'date',
        'comment',
    ];

    public function studentGroupPayment()
    {
        return $this->belongsTo(StudentGroupPayment::class);
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function paymentType(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(PaymentType::class);
    }

    public function creator(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
}
