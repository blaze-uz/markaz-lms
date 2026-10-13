<?php

namespace App\Models;

use App\Traits\HasBranch;

class Room extends BaseEntity
{
    use HasBranch;

    protected $fillable = [
        'name',
        'capacity',
        'status',
        'branch_id',
    ];
}
