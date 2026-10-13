<?php

namespace App\Models;

use App\Traits\HasBranch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeadCollection extends Model
{
    use HasBranch, HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'type',
        'order',
        'kanban_collection_id',
        'board_url',
    ];

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class, 'collection_id');
    }

    public function boards(): HasMany
    {
        return $this->hasMany(LeadBoard::class, 'lead_collection_id');
    }
}
