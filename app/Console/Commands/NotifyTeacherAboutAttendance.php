<?php

namespace App\Console\Commands;

use App\Models\Group;
use DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Console\Command;

class NotifyTeacherAboutAttendance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notify:teacher-about-attendance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notify teacher about attendance';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $chatId = settings('telegram_staff_group_id');

        if (!$chatId) {
            $this->info('Telegram staff group ID not configured ❌');
            return;
        }

        $chat = TelegraphChat::where('id', $chatId)->first();

        if (!$chat) {
            $this->error("Chat not found: chat_id({$chatId})");
            return;
        }

        $date = now()->format('Y-m-d');

        // Get groups without attendance for today
        $groups = Group::where('active', true)
            ->whereDoesntHave('attendances', function ($query) use ($date) {
                $query->where('date', $date);
            })
            ->get();

        if ($groups->isEmpty()) {
            $this->info('All groups have attendance records for today ✅');
            return;
        }

        $message = $this->buildMessage($date, $groups);

        try {
            $response = $chat->message($message)->send();
            $this->info('Notification sent successfully ✅');
            info('Telegram notification response: ' . json_encode($response));
        } catch (\Exception $e) {
            $this->error('Failed to send notification: ' . $e->getMessage());
            info('Telegram notification error: ' . $e->getMessage());
        }
    }

    /**
     * Build the notification message
     */
    private function buildMessage(string $date, $groups): string
    {
        $message = "Bugun <b>{$date}</b> yo'qlamalarni <b>10:30</b> gacha o'tkazib qo'yishingizni so'rayman!:\n\n";
        $message .= "❌ hozirgacha yo'qlama qilinmagan guruhlar:\n";

        foreach ($groups as $group) {
            $message .= "👉 <b>{$group->name}</b>\n";
        }

        return $message;
    }
}
