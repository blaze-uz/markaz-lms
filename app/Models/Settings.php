<?php

namespace App\Models;

use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Settings extends BaseEntity implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'key',
        'value',
    ];
}
