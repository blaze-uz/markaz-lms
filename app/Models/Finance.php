<?php

namespace App\Models;

use App\Enums\FinanceTypeEnum;
use Illuminate\Database\Eloquent\SoftDeletes;

class Finance extends BaseEntity
{
    use SoftDeletes;

    protected $fillable = [
        'type',
        'amount',
        'financeable_id',
        'financeable_type',
        'created_by',
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i',
        'type' => FinanceTypeEnum::class
    ];

    public function transaction(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function financeable(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo();
    }
}
