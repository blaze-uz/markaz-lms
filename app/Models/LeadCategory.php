<?php

namespace App\Models;

use App\Traits\HasBranch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeadCategory extends Model
{
    use HasFactory, HasBranch, SoftDeletes;

    protected $fillable = [
        'name',
        'lead_collection_id',
        'url',
        'group_id',
        'form_comment_state',
        'form_course_state',
        'success_message',
        'kanban_board_id',
    ];
}
