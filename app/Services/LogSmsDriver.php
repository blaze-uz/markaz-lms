<?php

namespace App\Services;

use App\Models\Sms;
use App\Models\SmsTemplate;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class LogSmsDriver implements SmsDriver
{
    public function send(Sms $sms, string $phone, string $content): array
    {
        Log::info('SMS', ['sms_id' => $sms->id, 'phone' => $phone, 'message' => $content]);

        return ['status_code' => 200, 'status' => 'sent'];
    }

    public function bulk(Collection $sms, string $dispatchId): array
    {
        foreach ($sms as $message) {
            $this->send($message, $message->phone, $message->content);
        }

        return ['status_code' => 200, 'status' => 'sent'];
    }

    public function submitTemplate(SmsTemplate $template): void
    {
        $template->update([
            'status' => SmsTemplate::STATUS_REGISTERED,
            'sent_to_moderation_at' => now(),
            'moderation_response_at' => now(),
        ]);
    }

    public function checkTemplate(SmsTemplate $template): void
    {
        $template->update(['status' => SmsTemplate::STATUS_REGISTERED]);
    }
}
