<?php

namespace App\Models;

class UserContract extends BaseEntity
{
    protected $fillable = [
        'user_id',
        'amount',
        'payment_day'
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function users(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_user_contract')->withPivot('paid_at', 'transaction_id');
    }
}
