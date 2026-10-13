<?php

namespace App\Services;

use App\Models\Answer;
use App\Models\Test;
use Illuminate\Support\Facades\DB;

class TestService
{
    public function store($data, $quiz_id)
    {
        DB::transaction(function () use ($data, $quiz_id) {
            $data['quiz_id'] = $quiz_id;
            $test = Test::create($data);
            foreach ($data['answers'] as $key => $answer) {
                Answer::create([
                    'answer' => $answer,
                    'is_correct' => $key == $data['is_correct'],
                    'test_id' => $test->id
                ]);
            }
        });
    }

    public function destroy($id)
    {
        DB::transaction(function () use ($id) {
            $test = Test::find($id);
            if (!$test) {
                return;
            }
            $test->answers()->delete();
            $test->delete();
        });
    }
}
