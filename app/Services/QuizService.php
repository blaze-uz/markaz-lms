<?php

namespace App\Services;

use App\Models\Answer;
use App\Models\Quiz;
use App\Models\QuizResult;
use App\Models\Settings;
use App\Models\Test;
use DefStudio\Telegraph\Models\TelegraphBot;
use Illuminate\Support\Facades\DB;

class QuizService
{
    public function store($data)
    {
        $data['created_by'] = auth()->id();
        $quiz = Quiz::create($data);

        return $quiz;
    }

    public function show($id)
    {
        return Quiz::findOrFail($id);
    }

    public function update($data, $id)
    {
        $quiz = Quiz::findOrFail($id);
        $quiz->update($data);

        return $quiz;
    }

    public function setToken($data)
    {
        DB::beginTransaction();
        try {
            $setting = Settings::where('key', 'quiz_telegram_bot_token')->first();
            if ($setting) {
                $setting->update(['value' => $data['bot_token']]);
            } else {
                Settings::create([
                    'key' => 'quiz_telegram_bot_token',
                    'value' => $data['bot_token'],
                ]);
            }

            $bot = TelegraphBot::where('token', $data['bot_token'])->first();
            if ($bot) {
                $bot->unregisterWebhook()->send();
                $bot->delete();
            }

            $this->registerBot($data);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function registerBot($data)
    {
        $bot = TelegraphBot::create([
            'token' => $data['bot_token'],
            'name' => 'Quiz Bot',
        ]);
        $bot->registerWebhook()->send();
    }
    
    public function getQuizzes()
    {
        $emodjis = $this->getEmodjis();
        $quizzes = Quiz::select('id', 'name')->orderBy('name')->get();

        foreach ($quizzes as $quiz) {
            $quiz->emodji = $emodjis[array_rand($emodjis)];
        }

        return $quizzes;
    }

    public function getQuiz($id = null)
    {
        if (!$id) {
            return null;
        }

        $quiz = Quiz::findOrFail($id);
        $quiz->emodji = $this->getEmodjis()[array_rand($this->getEmodjis())];

        return $quiz;
    }

    public function getEmodjis()
    {
        return [
            '📖',
            '📚',
            '📙',
            '🎓'
        ];
    }

    public function generateQuiz($quiz_id)
    {
        DB::beginTransaction();
        try {
            $unique_id = uniqid();

            $quiz = Quiz::findOrFail($quiz_id);
            $questions = Test::with(['answers' => function ($query) {
                $query->inRandomOrder();
            }])->where('quiz_id', $quiz_id)
                ->inRandomOrder()
                ->limit($quiz->tests_count)
                ->get();

            $quiz->tests = $questions;
            $quiz->unique_id = $unique_id;
            $result = QuizResult::create([
                'quiz_id' => $quiz_id,
                'unique_id' => $unique_id,
                'score' => 0,
                'status' => 'pending',
                'started_at' => now(),
            ]);
            DB::commit();
            return $quiz;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function save($data)
    {
        DB::beginTransaction();
        try {
            $result = QuizResult::where('unique_id', $data['uniqueId'])->first();
            $quiz = Quiz::findOrFail($data['quizId']);
            $score = 0;
            $percent = 0;
            foreach ($data['answers'] as $answer) {
                $answer = Answer::findOrFail($answer);
                if ($answer->is_correct) {
                    $score++;
                }
            }
            $percent = ($score / $quiz->tests_count) * 100;
            $status = $percent >= $quiz->passing_percentage ? 'passed' : 'failed';
            $result->update([
                'score' => intval($percent),
                'status' => $status,
                'finished_at' => now(),
            ]);
            DB::commit();
            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getResult($unique_id)
    {
        return QuizResult::where('unique_id', $unique_id)->first();
    }
}
