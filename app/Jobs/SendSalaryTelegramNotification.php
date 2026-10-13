<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\UserTelegram;
use DefStudio\Telegraph\Models\TelegraphBot;
use DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Support\Facades\Log;

class SendSalaryTelegramNotification extends BaseJob
{
    protected $amount;

    protected $user_id;

    protected $creator_id;

    public function __construct($amount, $user_id)
    {
        $this->amount = $amount;
        $this->user_id = $user_id;
        $this->creator_id = auth()->id();
    }

    public function handle(): void
    {
        if (empty($this->user_id)) {
            Log::warning('User ID is missing for salary notification');

            return;
        }

        // Get the Staff telegram bot
        $staffBot = TelegraphBot::where('name', 'Staff app')->first();

        if (! $staffBot) {
            Log::error('Staff telegram bot not found');

            return;
        }

        // Get user's active telegram connections
        $telegramConnections = UserTelegram::where('user_id', $this->user_id)
            ->where('status', 'active')
            ->get();

        if ($telegramConnections->isEmpty()) {
            Log::info('No active telegram connections found for user', [
                'user_id' => $this->user_id,
            ]);

            return;
        }

        foreach ($telegramConnections as $connection) {
            $this->sendNotification($connection, $staffBot);
        }
    }

    protected function sendNotification($connection, $staffBot)
    {
        try {
            $message = $this->formatMessage();

            // Find or create chat for this user with the Staff bot
            $chat = TelegraphChat::firstOrCreate([
                'chat_id' => $connection->chat_id,
                'telegraph_bot_id' => $staffBot->id,
            ], [
                'name' => 'Staff Bot Chat',
            ]);

            // Send message using the Staff bot
            $chat->message($message)->send();

            Log::info('Salary notification sent successfully', [
                'user_id' => $this->user_id,
                'chat_id' => $connection->chat_id,
                'bot_name' => $staffBot->name,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send telegram notification', [
                'user_id' => $this->user_id,
                'chat_id' => $connection->chat_id,
                'bot_name' => $staffBot->name ?? 'Unknown',
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function formatMessage()
    {
        $date = now()->format('d.m.Y');
        $creator = User::find($this->creator_id);
        $creatorName = $creator ? $creator->name : 'Tizim';

        $user = User::with('account')->find($this->user_id);
        $balance = $user ? money($user->account->balance) : '0';

        return "💰 <b>Hisobingizga to'lov o'tkazildi!</b>\n\n".
            '💵 Summa: <b>'.money($this->amount)."</b>\n".
            "📅 Sana: <b>{$date}</b>\n".
            "👤 To'lovni amalga oshirdi: <b>{$creatorName}</b>\n".
            "💳 Joriy balans: <b>{$balance}</b>\n\n".
            '📱 Staff Bot orqali yuborildi';
    }

    public function failed(\Throwable $exception)
    {
        Log::error('Telegram salary notification failed', [
            'user_id' => $this->user_id,
            'amount' => $this->amount,
            'error' => $exception->getMessage(),
        ]);
    }
}
