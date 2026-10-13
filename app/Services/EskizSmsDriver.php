<?php

namespace App\Services;

use App\Models\Sms;
use App\Models\SmsTemplate;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class EskizSmsDriver implements SmsDriver
{
    public function send(Sms $sms, string $phone, string $content): array
    {
        $response = $this->request('POST', '/api/message/sms/send', [
            'user_sms_id' => (string) $sms->id,
            'mobile_phone' => preg_replace('/\D/', '', $phone),
            'message' => $content,
            'from' => config('sms.eskiz.from'),
            'callback_url' => $this->callbackUrl(),
        ], true);

        return [
            'status_code' => $response->status(),
            'status' => 'sending',
            'external_id' => $response->json('id') ?? $response->json('data.sms_id') ?? $response->json('data.id'),
            'error' => $response->json('message') ?? 'HTTP '.$response->status().': Failed to send SMS',
        ];
    }

    public function bulk(Collection $sms, string $dispatchId): array
    {
        $response = $this->request('POST', '/api/message/sms/send-batch', [
            'messages' => $sms->map(fn (Sms $message): array => [
                'user_sms_id' => (string) $message->id,
                'to' => preg_replace('/\D/', '', $message->phone),
                'text' => $message->content,
            ])->values()->all(),
            'from' => config('sms.eskiz.from'),
            'dispatch_id' => $dispatchId,
            'callback_url' => $this->callbackUrl(),
        ]);

        return [
            'status_code' => $response->status(),
            'status' => 'sending',
            'batch_id' => $response->json('data.dispatch_id') ?? $response->json('dispatch_id') ?? $response->json('id'),
            'error' => $response->json('message') ?? 'HTTP '.$response->status().': Failed to send SMS',
        ];
    }

    public function submitTemplate(SmsTemplate $template): void
    {
        $response = $this->request('POST', '/api/user/template', ['template' => $template->content], true);
        $response->throw();

        $template->update([
            'status' => SmsTemplate::STATUS_MODERATION,
            'eskiz_template_id' => $response->json('data.id'),
            'sample' => $template->content,
            'sent_to_moderation_at' => now(),
        ]);
    }

    public function checkTemplate(SmsTemplate $template): void
    {
        $response = $this->request('GET', '/api/user/templates');
        $response->throw();
        $item = collect($response->json('result', []))->firstWhere('id', $template->eskiz_template_id);

        if (! $item) {
            return;
        }

        $status = match ($item['status']) {
            'service', 'reklama' => SmsTemplate::STATUS_REGISTERED,
            'rejected' => SmsTemplate::STATUS_NOT_REGISTERED,
            default => SmsTemplate::STATUS_MODERATION,
        };

        $template->update([
            'status' => $status,
            'eskiz_status' => $item['status'],
            'template' => $item['original_text'] ?? $template->template,
            'moderation_response_at' => now(),
        ]);
    }

    private function request(string $method, string $path, array $data = [], bool $multipart = false): Response
    {
        $send = function () use ($method, $path, $data, $multipart): Response {
            $request = Http::acceptJson()->withToken($this->token())->timeout(30);

            if ($multipart) {
                $request->asMultipart();
            }

            return $request->send($method, rtrim(config('sms.eskiz.base_url'), '/').$path, [
                $method === 'GET' ? 'query' : ($multipart ? 'multipart' : 'json') => $data,
            ]);
        };

        $response = $send();

        if (in_array($response->status(), [401, 403], true)) {
            Cache::forget($this->tokenKey());
            $response = $send();
        }

        return $response;
    }

    private function token(): string
    {
        return Cache::remember($this->tokenKey(), now()->addDays(25), function (): string {
            $response = Http::asForm()->acceptJson()->timeout(30)->post(rtrim(config('sms.eskiz.base_url'), '/').'/api/auth/login', [
                'email' => config('sms.eskiz.email'),
                'password' => config('sms.eskiz.password'),
            ]);
            $response->throw();

            return $response->json('data.token');
        });
    }

    private function tokenKey(): string
    {
        return 'sms.eskiz.token.'.sha1(config('sms.eskiz.base_url').'|'.config('sms.eskiz.email'));
    }

    private function callbackUrl(): string
    {
        return route('sms.eskiz.callback').'?token='.rawurlencode((string) config('sms.eskiz.callback_token'));
    }
}
