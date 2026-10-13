<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Exam extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'tests_count',
        'created_by',
    ];

    public static function search($search)
    {
        return empty($search) ? static::query()
            : static::query()->where('name', 'ilike', '%' . $search . '%');

    }

    public function creator(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function details(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ExamDetail::class);
    }

    public function studentExams(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(StudentExam::class);
    }
}
