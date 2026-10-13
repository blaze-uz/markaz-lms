<?php

namespace App\Models;

class ValidationSettings extends BaseEntity
{
    protected $fillable = [
        'group',
        'name',
        'required',
    ];
}
