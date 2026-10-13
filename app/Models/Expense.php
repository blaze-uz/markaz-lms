<?php

namespace App\Models;

use App\Traits\HasBranch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Expense extends Model
{
    use SoftDeletes, HasBranch, LogsActivity;

    protected $fillable = [
        'item_id',
        'quantity',
        'price',
        'place_id',
        'account_id',
        'created_by',
        'payment_type_id',
        'date',
        'description',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'item_id',
                'quantity',
                'price',
                'place_id',
                'account_id',
                'payment_type_id',
                'date',
                'description',
            ])->logOnlyDirty();
    }

    public function getLogNameToUse(): string
    {
        return 'expenses';
    }

    public function item(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ExpenseItem::class);
    }

    public function place(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ExpensePlace::class);
    }

    public function account(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function createdBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeSearch($query, $search)
    {
        return empty($search) ? $query
            : $query->whereRelation('item', 'name', 'ilike', '%' . $search . '%')
            ->orWhereRelation('place', 'name', 'ilike', '%' . $search . '%')
            ->orWhereRelation('account', 'name', 'ilike', '%' . $search . '%');
    }

    public function transaction(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ExpenseTransaction::class);
    }

    public function paymentType(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(PaymentType::class);
    }
}
