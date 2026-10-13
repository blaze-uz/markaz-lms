<?php

namespace App\Jobs;

use App\Models\Sms;
use App\Services\SmsDriver;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Log;

class SendSms extends BaseJob
{
    public $queue = 'sms';

    private const MAX_TRANSPORT_ATTEMPTS = 3;

    public function __construct(
        protected int $sms_id,
        protected string $phone,
        protected string $content,
    ) {
    }

    public function handle(): void
    {
        if ($secondsRemaining = isRateLimited('sms-api-limit')) {
            $this->release($secondsRemaining);

            return;
        }

        $sms = Sms::find($this->sms_id);
        if (! $sms || $sms->status === 'sent' || ($sms->status === 'sending' && $sms->external_id !== null)) {
            return;
        }

        if (settingsFromDb('send_sms') !== 'enable') {
            $this->markFailed($sms, 'SMS sending is disabled');

            return;
        }

        try {
            $result = app(SmsDriver::class)->send($sms, $this->phone, $this->content);
        } catch (ConnectionException $exception) {
            $result = ['status_code' => 0, 'error' => $exception->getMessage()];
        }

        if ($result['status_code'] === 429) {
            setRateLimit('sms-api-limit', 61);
            $this->release(61);

            return;
        }

        if ($result['status_code'] < 200 || $result['status_code'] >= 300) {
            $permanentFailure = in_array($result['status_code'], [400, 401, 403, 404, 405, 422], true);

            if (! $permanentFailure && $this->attempts() < self::MAX_TRANSPORT_ATTEMPTS) {
                $this->release(60);

                return;
            }

            $this->markFailed($sms, $result['error']);

            return;
        }

        Sms::query()->whereKey($sms->id)->whereIn('status', ['pending', 'sending'])->update([
            'status' => $result['status'],
            'channel' => config('sms.driver'),
            'external_id' => $result['external_id'] ?? null,
            'sent_at' => $result['status'] === 'sent' ? now() : null,
            'error_log' => null,
        ]);
    }

    private function markFailed(Sms $sms, string $errorMessage): void
    {
        $sms->update([
            'status' => 'failed',
            'error_log' => $errorMessage,
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('SendSms job failed', ['sms_id' => $this->sms_id, 'error' => $exception->getMessage()]);
        $sms = Sms::find($this->sms_id);

        if ($sms && $sms->status !== 'sent' && $sms->external_id === null) {
            $this->markFailed($sms, 'Job failed: '.$exception->getMessage());
        }
    }
}
