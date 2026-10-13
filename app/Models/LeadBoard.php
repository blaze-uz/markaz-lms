<?php

namespace App\Models;

use App\Traits\HasBranch;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class LeadBoard extends BaseEntity
{
    use HasBranch, LogsActivity;

    protected $fillable = [
        'name',
        'lead_collection_id',
        'url',
        'group_id',
        'success_message',
        'form_comment_state',
        'form_course_state',
        'kanban_board_id'
    ];

    protected $casts = [
        'form_comment_state' => 'boolean',
        'form_course_state' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'lead_collection_id', 'url', 'group_id', 'success_message', 'form_comment_state', 'form_course_state'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function leadCollection()
    {
        return $this->belongsTo(LeadCollection::class);
    }

    public function leads()
    {
        return $this->hasMany(Lead::class, 'board_id');
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function kanbanBoard()
    {
        return $this->belongsTo(KanbanBoard::class);
    }
}
