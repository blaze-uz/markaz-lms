<?php

namespace App\Models;

class KanbanTaskCall extends BaseEntity
{
    protected $fillable = [
        'kanban_task_id',
        'status',
        'comment',
        'causer_id'
    ];

    public function causer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'causer_id');
    }

    public function lead(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(KanbanTask::class, 'kanban_task_id');
    }
}
