<?php

namespace App\Livewire\Settings;

use App\Models\Telegram;
use Livewire\Component;

class TelegramDailyReportItems extends Component
{
    public function render()
    {
        $existingTelegrams = Telegram::whereHas('telegram_templates', function ($query) {
            $query->where('type', 'daily_report');
        })->get();
        return view('livewire.settings.telegram-daily-report-items', [
            'existingTelegrams' => $existingTelegrams,
        ]);
    }
}
