<?php

namespace App\Models;

use App\Traits\UsesDynamicConnection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SmsGroup extends Model
{
    use HasFactory, UsesDynamicConnection;

    public function sms()
    {
        return $this->hasMany(Sms::class);
    }

    public function batches(): HasMany
    {
        return $this->hasMany(SmsBatch::class);
    }
}
