<?php

namespace App\Jobs;

use App\Models\UserTelegram;
use DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Support\Facades\Log;

class SendPaymentTelegramNotification extends BaseJob
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function handle(): void
    {
        if (empty($this->data['teacher_id'])) {
            Log::warning('Teacher ID is missing for payment notification', $this->data);

            return;
        }

        // Get teacher's active telegram connections
        $telegramConnections = UserTelegram::where('user_id', $this->data['teacher_id'])
            ->where('status', 'active')
            ->get();

        if ($telegramConnections->isEmpty()) {
            Log::info('No active telegram connections found for teacher', [
                'teacher_id' => $this->data['teacher_id'],
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
            Log::error('Failed to send telegram notification to teacher', [
                'teacher_id' => $this->data['teacher_id'],
                'chat_id' => $connection->chat_id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function formatMessage()
    {
        $date = date('d.m.Y', strtotime($this->data['date']));
        $studentName = $this->data['student_name'];
        $paymentType = $this->data['payment_type'];

        return "💰 <b>Yangi to'lov</b>\n\n".
            "👤 O'quvchi: <b>{$studentName}</b>\n".
            "💳 To'lov turi: <b>{$paymentType}</b>\n".
            "💵 Summa: <b>{$this->data['amount']}</b>\n".
            "📅 Sana: <b>{$date}</b>";
    }

    public function failed(\Throwable $exception)
    {
        Log::error('Telegram payment notification failed', [
            'teacher_id' => $this->data['teacher_id'],
            'student_name' => $this->data['student_name'],
            'amount' => $this->data['amount'],
            'error' => $exception->getMessage(),
        ]);
    }
}
