<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentExamDetail extends Model
{
    protected $fillable = [
        'student_id',
        'exam_id',
        'exam_detail_id',
        'student_exam_id',
        'score',
        'correct_answers',
    ];

    public function student(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function exam(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function examDetail(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ExamDetail::class);
    }
}
