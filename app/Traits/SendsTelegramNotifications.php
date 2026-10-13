<?php

namespace App\Traits;

use Illuminate\Support\Facades\Log;

trait SendsTelegramNotifications
{
    protected function sendTelegramNotification($job, $amount, $user_id, $type = 'notification')
    {
        try {
            $telegram_bot_token = settings('telegram_bot_token');

            if ($telegram_bot_token) {
                $job::dispatch($amount, $user_id);
            }
        } catch (\Exception $e) {
            // Log the error but don't stop the payment process
            Log::error("Failed to send telegram {$type}: " . $e->getMessage(), [
                'user_id' => $user_id,
                'type' => $type,
            ]);
        }
    }
}
