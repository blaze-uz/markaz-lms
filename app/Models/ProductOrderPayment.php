<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductOrderPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_order_id',
        'student_id',
        'amount',
        'payment_date',
        'payment_type_id',
        'notes',
        'created_by',
    ];

    public function paymentType()
    {
        return $this->belongsTo(PaymentType::class, 'payment_type_id');
    }

    public function productOrder()
    {
        return $this->belongsTo(ProductOrder::class, 'product_order_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
