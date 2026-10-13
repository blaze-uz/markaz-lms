<?php

namespace App\Livewire\Settings;

use App\Models\Telegram;
use App\Models\TelegramTemplate;
use Livewire\Component;

class TelegramDailyReportSettings extends Component
{
    public function render()
    {
        $telegramDailyReportStatus = TelegramTemplate::where('type', 'daily_report')->first()->status;
        $telegramDailyReportTime = TelegramTemplate::where('type', 'daily_report')->first()->time;
        $telegrams = Telegram::orderBy('name')->get();
        return view('livewire.settings.telegram-daily-report-settings', [
            'telegramDailyReportStatus' => $telegramDailyReportStatus,
            'telegrams' => $telegrams,
            'telegramDailyReportTime' => $telegramDailyReportTime,
        ]);
    }
}
