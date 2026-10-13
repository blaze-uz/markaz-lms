<?php

namespace App\Models;

use App\Traits\HasBranch;

class KanbanBoard extends BaseEntity
{
    use HasBranch;

    protected $fillable = [
        'name',
        'kanban_collection_id',
        'url',
        'group_id',
        'success_message',
        'form_comment_state',
        'form_course_state'
    ];

    public function kanbanCollection()
    {
        return $this->belongsTo(KanbanCollection::class);
    }

    public function tasks()
    {
        return $this->hasMany(KanbanTask::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }
}
