<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TelegramTemplate extends Model
{
    protected $fillable = [
        'type',
        'status',
        'time'
    ];

    public function telegrams(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Telegram::class);
    }
}
