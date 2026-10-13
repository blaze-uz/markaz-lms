<?php

namespace App\Models;

class GroupAttendanceSettings extends BaseEntity
{
    protected $fillable = [
        'group_id',
        'auto_send',
        'after_minutes',
        'send_to',
    ];

    public function group(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Group::class);
    }
}
