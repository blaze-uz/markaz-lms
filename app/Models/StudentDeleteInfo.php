<?php

namespace App\Models;

class StudentDeleteInfo extends BaseEntity
{
    protected $fillable = [
        'student_id',
        'deleted_by',
        'deleted_at',
        'comment',
    ];

    public function deletedBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}
