<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\UserTelegram;
use DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Support\Facades\Log;

class SendWithdrawTelegramNotification extends BaseJob
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
            Log::warning('User ID is missing for withdraw notification');

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
            $this->sendNotification($connection);
        }
    }

    protected function sendNotification($connection)
    {
        try {
            $message = $this->formatMessage();
            $telegram_bot_token = settingsFromDb('telegram_bot_token');

            if (empty($telegram_bot_token)) {
                throw new \Exception('Telegram bot token not configured');
            }

            // Create chat instance
            $chats = TelegraphChat::where('chat_id', $connection->chat_id)->get();

            foreach ($chats as $chat) {
                $chat->message($message)->send();
            }
        } catch (\Exception $e) {
            Log::error('Failed to send telegram notification', [
                'user_id' => $this->user_id,
                'chat_id' => $connection->chat_id,
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

        return "🔴 <b>Hisobingizdan pul yechib berildi!</b>\n\n".
            '💵 Summa: <b>'.money($this->amount)."</b>\n".
            "📅 Sana: <b>{$date}</b>\n".
            "👤 Amalga oshirdi: <b>{$creatorName}</b>\n".
            "💳 Joriy balans: <b>{$balance}</b>\n\n";
    }

    public function failed(\Throwable $exception)
    {
        Log::error('Telegram withdraw notification failed', [
            'user_id' => $this->user_id,
            'amount' => $this->amount,
            'error' => $exception->getMessage(),
        ]);
    }
}
