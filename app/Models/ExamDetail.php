<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamDetail extends Model
{
    protected $fillable = [
        'exam_id',
        'subject',
        'score',
    ];

    public function exam(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }
}
