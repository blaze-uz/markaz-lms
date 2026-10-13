<?php

namespace App\Models;

use App\Traits\HasBranch;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends BaseEntity
{
    use HasBranch, SoftDeletes;

    protected $fillable = [
        'student_id',
        'payment_type_id',
        'amount',
        'created_by',
        'date',
        'transaction_id',
        'student_group_payment_id',
        'comment',
        'group_id',
    ];

    protected $casts = [
        'date' => 'timestamp',
    ];

    public static function search(mixed $search)
    {
        return empty($search) ? static::query()->with(['student' => fn ($query) => $query->withTrashed()])
            : static::query()->with('student')
                ->whereHas('student', fn ($query) => $query->where(
                    fn ($q) => $q->whereRaw("CONCAT(name, ' ', surname) ilike ?", ["%$search%"])
                        ->orWhereRaw("CONCAT(surname, ' ', name) ilike ?", ["%$search%"])
                )->orWhere('phone', 'ilike', "%$search%")->withTrashed());
    }

    public function student()
    {
        return $this->belongsTo(Student::class)->withTrashed();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function payment_type()
    {
        return $this->belongsTo(PaymentType::class);
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function payment_discounts()
    {
        return $this->hasMany(PaymentDiscount::class);
    }

    public function student_group_payment()
    {
        return $this->belongsTo(StudentGroupPayment::class)->withTrashed();
    }

    public function studentGroupPayment()
    {
        return $this->belongsTo(StudentGroupPayment::class)->withTrashed();
    }

    public function branch(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function refund(): HasOne
    {
        return $this->hasOne(RefundPayment::class, 'id', 'refund_id');
    }

    public function refunds(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(RefundPayment::class, 'payment_id', 'id');
    }

    public function group(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Group::class);
    }
}
