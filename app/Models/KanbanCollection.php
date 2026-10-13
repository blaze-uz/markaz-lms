<?php

namespace App\Models;

use App\Traits\HasBranch;

class KanbanCollection extends BaseEntity
{
    use HasBranch;

    protected $fillable = [
        'name',
    ];

    public function boards()
    {
        return $this->hasMany(KanbanBoard::class);
    }

    public function unsignedTasks()
    {
        return $this->hasMany(KanbanTask::class)->whereNull('kanban_board_id');
    }

    public function tasks()
    {
        return $this->hasMany(KanbanTask::class);
    }

    public static function search($search)
    {
        return empty($search) ? static::query()
            : static::query()->where('name', 'ilike', '%' . $search . '%');
    }
}
