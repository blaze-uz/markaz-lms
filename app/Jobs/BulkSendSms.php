<?php

namespace App\Jobs;

use App\Models\Sms;
use App\Services\SmsDriver;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BulkSendSms extends BaseJob
{
    public $queue = 'sms';

    private const MAX_TRANSPORT_ATTEMPTS = 3;

    protected string $dispatchId;

    public function __construct(
        protected array $smsIds,
        protected bool $throwOnFailure = false,
    ) {
        $this->smsIds = array_values(array_unique(array_filter($this->smsIds)));
        $this->dispatchId = (string) Str::uuid();
    }

    public function handle(): void
    {
        if ($this->smsIds === []) {
            return;
        }

        if ($secondsRemaining = isRateLimited('sms-api-limit')) {
            $this->release($secondsRemaining);

            return;
        }

        if (settingsFromDb('send_sms') !== 'enable') {
            $this->markAllFailed('SMS sending is disabled');

            return;
        }

        $smsRecords = Sms::query()->whereIn('id', $this->smsIds)->where('status', '!=', 'sent')->get();
        $order = array_flip($this->smsIds);
        $smsRecords = $smsRecords
            ->reject(fn (Sms $sms): bool => $sms->status === 'sending' && $sms->gateway_batch_id !== null)
            ->sortBy(fn (Sms $sms) => $order[$sms->id])->values();

        if ($smsRecords->isEmpty()) {
            return;
        }

        try {
            $result = app(SmsDriver::class)->bulk($smsRecords, $this->dispatchId);
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

            if (! $permanentFailure && ! $this->throwOnFailure && $this->attempts() < self::MAX_TRANSPORT_ATTEMPTS) {
                $this->release(60);

                return;
            }

            $this->markAllFailed($result['error']);

            return;
        }

        foreach ($smsRecords as $sms) {
            Sms::query()->whereKey($sms->id)->whereIn('status', ['pending', 'sending'])->update([
                'status' => $result['status'],
                'channel' => config('sms.driver'),
                'gateway_batch_id' => $result['batch_id'] ?? null,
                'sent_at' => $result['status'] === 'sent' ? now() : null,
                'error_log' => null,
            ]);
        }
    }

    private function markAllFailed(string $errorMessage): void
    {
        Sms::query()->whereIn('id', $this->smsIds)->where('status', '!=', 'sent')->whereNull('gateway_batch_id')->update([
            'status' => 'failed',
            'error_log' => $errorMessage,
        ]);

        if ($this->throwOnFailure) {
            throw new \RuntimeException($errorMessage);
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('BulkSendSms job failed', ['sms_ids' => $this->smsIds, 'error' => $exception->getMessage()]);

        Sms::query()->whereIn('id', $this->smsIds)->where('status', '!=', 'sent')->whereNull('gateway_batch_id')->update([
            'status' => 'failed',
            'error_log' => 'Job failed: '.$exception->getMessage(),
        ]);
    }
}
