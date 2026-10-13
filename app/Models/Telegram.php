<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Telegram extends Model
{
    protected $fillable = [
        'name',
        'telegram_id',
    ];

    public static function search($search)
    {
        return empty($search) ? static::query()
            : static::query()->where('name', 'ilike', '%' . $search . '%')
                ->orWhere('telegram_id', 'ilike', '%' . $search . '%');
    }

    public function telegram_templates(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(TelegramTemplate::class);
    }
}
