<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KanbanTaskComment extends Model
{
    protected $fillable = [
        'kanban_task_id',
        'user_id',
        'comment',
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function task(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(KanbanTask::class);
    }
}
