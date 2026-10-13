<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{
    protected $fillable = ['answer', 'test_id', 'is_correct'];

    public function test()
    {
        return $this->belongsTo(Test::class);
    }
}
