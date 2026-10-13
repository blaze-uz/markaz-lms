<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class StudentGroupPayment extends BaseEntity
{
    use LogsActivity, SoftDeletes;

    protected $fillable = [
        'student_id',
        'group_id',
        'date',
        'amount',
        'left_amount',
        'range',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'amount',
                'left_amount',
            ]);
    }

    public function getLogNameToUse(): string
    {
        return 'student_group_payment';
    }

    public function student(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function group(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Group::class)->withTrashed();
    }

    public function payments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function scopeNotPaid()
    {
        // left amount not equal to 0
        return $this->where('left_amount', '!=', 0);
    }

    public function transactions()
    {
        return $this->belongsToMany(Transaction::class);
    }

    public function refundPayments()
    {
        return $this->hasMany(RefundPayment::class);
    }
}
