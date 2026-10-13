<?php

namespace App\Models;

class ExpenseItem extends BaseEntity
{
    protected $fillable = [
        'name',
        'description',
    ];

    public static function search($search)
    {
        return empty($search) ? static::query()
            : static::query()->where('name', 'ilike', '%' . $search . '%')
                ->orWhere('description', 'ilike', '%' . $search . '%');
    }
}
