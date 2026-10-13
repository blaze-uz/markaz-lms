<?php

namespace App\Models;

class TextSuggestion extends BaseEntity
{
    protected $fillable = [
        'text',
        'type',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
