<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Test extends Model
{
    protected $fillable = [
        'question',
        'quiz_id',
    ];

    public function answers()
    {
        return $this->hasMany(Answer::class);
    }

    public function scopeSearch($query, $keyword)
    {
        return $query->where('question', 'ilike', '%' . $keyword . '%');
    }

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }
}
