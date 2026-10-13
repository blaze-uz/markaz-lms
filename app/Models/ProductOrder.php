<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_id',
        'total_price',
        'notes',
        'created_by',
        'left_amount',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function productOrderItems()
    {
        return $this->hasMany(ProductOrderItem::class);
    }
}
