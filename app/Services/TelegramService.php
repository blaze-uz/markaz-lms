<?php

namespace App\Services;

use App\Models\Student;
use App\Models\Telegram;
use App\Models\TelegramTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    public function create($data): void
    {
        Telegram::create([
            'name' => $data['name'],
            'telegram_id' => $data['telegram_id'],
        ]);
    }

    public function delete($id)
    {
        Telegram::destroy($id);
    }

    public function setDailyReport(array $data): void
    {
        if (isset($data['status'])) {
            $status = $data['status'] === 'on' ? 1 : 0;

            DB::transaction(function () use ($data, $status) {
                if (isset($data['time'])) {
                    $timeSetting = TelegramTemplate::where('type', 'daily_report')->first();
                    if ($timeSetting) {
                        $timeSetting->time = $data['time'];
                        $timeSetting->save();
                    }
                }

                if (! empty($data['telegram_ids'])) {
                    $telegram_template = TelegramTemplate::where('type', 'daily_report')->first();
                    foreach ($data['telegram_ids'] as $telegram_id) {
                        $telegram_template->telegrams()->syncWithoutDetaching($telegram_id);
                    }
                }

                $dailyReportStatus = TelegramTemplate::where('type', 'daily_report')->first();
                if ($dailyReportStatus) {
                    $dailyReportStatus->status = $status;
                    $dailyReportStatus->save();
                }
            });
        } else {
            $status = 0;
            $dailyReportStatus = TelegramTemplate::where('type', 'daily_report')->first();
            if ($dailyReportStatus) {
                $dailyReportStatus->status = $status;
                $dailyReportStatus->save();
            }
        }

        cache()->forget('telegram_templates');
    }

    public function deleteDailyReportItem($telegram_id)
    {
        $telegram = Telegram::find($telegram_id);
        if ($telegram) {
            $telegram->telegram_templates()->detach();
        }
    }

    public function send($data): void
    {
        Artisan::call('app:daily-monitor-command', [
            'date' => $data['date'],
        ]);
    }

    public function getReceivers(Request $request)
    {
        $ids = json_decode($request->sendTelegramIds, true);
        if (! $ids) {
            Log::error('Failed to decode student IDs for Telegram');
            abort(404);
        }

        $receivers = Student::whereIn('id', $ids)
            ->orderBy('name')
            ->get();

        return $receivers;
    }
}
