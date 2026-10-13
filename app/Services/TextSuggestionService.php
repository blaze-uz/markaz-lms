<?php

namespace App\Services;

use App\Models\TextSuggestion;

class TextSuggestionService
{
    public function store(array $data): void
    {
        TextSuggestion::create([
            'text' => $data['text'],
            'type' => $data['type'],
            'user_id' => auth()->id(),
        ]);
    }

    public function delete($id)
    {
        TextSuggestion::find($id)->delete();
    }
}