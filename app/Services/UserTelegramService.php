<?php

namespace App\Services;

use App\Models\UserMessage;
use App\Models\UserTelegram;
use DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Support\Facades\Log;

class UserTelegramService
{

    public function sendTelegramMessage($message, $id)
    {
        $telegramConnections = UserTelegram::where('user_id', $id)
            ->where('status', 'active')
            ->get();

        $message = $this->formatMessage($message);

        foreach ($telegramConnections as $connection) {
            $this->sendNotification($connection, $message);
        }
    }

    public function sendNotification($connection, $message)
    {
        try {
            $telegram_bot_token = settings('telegram_bot_token');

            if (empty($telegram_bot_token)) {
                return;
            }

            // Create chat instance
            $chats = TelegraphChat::where('chat_id', $connection->chat_id)->get();

            foreach ($chats as $chat) {
                UserMessage::create([
                    'user_id' => $connection->user_id,
                    'message' => $message,
                    'send_type' => 'telegram',
                    'created_by' => auth()->id()
                ]);

                $chat->message($message)->send();
            }
        } catch (\Exception $e) {
            Log::error('Failed to send telegram notification to teacher', [
                'error' => $e->getMessage()
            ]);
        }
    }

    public function formatMessage($message)
    {
        $message .= "\n\n" . __('Yuboruvchi') . ': <b>' . auth()->user()->name . '</b>';

        return $message;
    }
}
