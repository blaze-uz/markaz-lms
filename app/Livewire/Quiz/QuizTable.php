<?php

namespace App\Livewire\Quiz;

use App\Models\Quiz;
use App\Models\Settings;
use Livewire\Component;

class QuizTable extends Component
{
    public function render()
    {
        $bot_token = Settings::where('key', 'quiz_telegram_bot_token')->first();
        if (!$bot_token) {
            $bot_token = Settings::create([
                'key' => 'quiz_telegram_bot_token',
                'value' => null,
            ]);
        }

        $items = Quiz::all();
        return view('livewire.quiz.quiz-table', [
            'items' => $items,
            'bot_token' => $bot_token,
        ]);
    }
}
