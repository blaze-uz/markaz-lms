<?php

namespace Tests\Feature;

use App\Jobs\BulkSendSms;
use App\Jobs\SendSms;
use App\Models\Settings;
use App\Models\Sms;
use App\Models\SmsTemplate;
use App\Models\User;
use App\Services\SmsTemplateService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class SmsDriverTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        Http::fake([]);
        Http::preventStrayRequests();
        config([
            'sms.driver' => 'eskiz',
            'sms.eskiz.base_url' => 'https://sms.example.test',
            'sms.eskiz.email' => 'sms@example.test',
            'sms.eskiz.password' => 'test-password',
            'sms.eskiz.from' => '4546',
            'sms.eskiz.callback_token' => 'callback-secret',
        ]);
        Settings::updateOrCreate(['key' => 'send_sms'], ['value' => 'enable']);
    }

    public function test_log_driver_sends_without_network_and_registers_templates(): void
    {
        config(['sms.driver' => 'log']);
        Log::spy();
        $sms = $this->makeSms();

        (new SendSms($sms->id, $sms->phone, $sms->content))->handle();
        (new SendSms($sms->id, $sms->phone, $sms->content))->handle();

        $this->assertSame('sent', $sms->refresh()->status);
        $this->assertNotNull($sms->sent_at);
        $this->assertSame('log', $sms->channel);
        Log::shouldHaveReceived('info')->once()->with('SMS', [
            'sms_id' => $sms->id,
            'phone' => $sms->phone,
            'message' => $sms->content,
        ]);
        $template = SmsTemplate::create(['name' => 'Notice', 'content' => 'Salom {STUDENT}', 'status' => 'not_registered', 'type' => 'student']);
        app(SmsTemplateService::class)->sendToModeration($template->id);
        $this->assertSame(SmsTemplate::STATUS_REGISTERED, $template->refresh()->status);
        Http::assertNothingSent();
    }

    public function test_single_send_is_multipart_and_refreshes_token_once_on_401(): void
    {
        Http::fake([
            'sms.example.test/api/auth/login' => Http::sequence()
                ->push(['data' => ['token' => 'first-token']])
                ->push(['data' => ['token' => 'new-token']]),
            'sms.example.test/api/message/sms/send' => Http::sequence()
                ->push(['message' => 'Expired token'], 401)
                ->push(['data' => ['sms_id' => 'message-123']], 200),
        ]);
        $sms = $this->makeSms(['phone' => '+998 (90) 123-45-67']);

        (new SendSms($sms->id, $sms->phone, $sms->content))->handle();

        Http::assertSent(fn (Request $request): bool => $request->url() === 'https://sms.example.test/api/auth/login'
            && $request->hasHeader('Content-Type', 'application/x-www-form-urlencoded')
            && $request['email'] === 'sms@example.test'
            && $request['password'] === 'test-password');
        Http::assertSent(function (Request $request) use ($sms): bool {
            $fields = collect($request->data())->pluck('contents', 'name');

            return $request->url() === 'https://sms.example.test/api/message/sms/send'
                && str_starts_with($request->header('Content-Type')[0], 'multipart/form-data')
                && $request->hasHeader('Authorization', 'Bearer new-token')
                && $fields->get('user_sms_id') === (string) $sms->id
                && $fields->get('mobile_phone') === '998901234567'
                && $fields->get('message') === 'Salom'
                && $fields->get('from') === '4546'
                && $fields->get('callback_url') === route('sms.eskiz.callback').'?token=callback-secret';
        });
        Http::assertSentCount(4);
        $this->assertSame('sending', $sms->refresh()->status);
        $this->assertSame('message-123', $sms->external_id);
    }

    public function test_batch_sends_json_and_retains_dispatch_id_for_retry(): void
    {
        Http::fake([
            'sms.example.test/api/auth/login' => Http::response(['data' => ['token' => 'token']]),
            'sms.example.test/api/message/sms/send-batch' => Http::sequence()
                ->push(['message' => 'Unavailable'], 500)
                ->push(['data' => ['dispatch_id' => 'dispatch-123']]),
        ]);
        $first = $this->makeSms();
        $second = $this->makeSms(['phone' => '+998901234568', 'content' => 'Xabar']);
        $job = new BulkSendSms([$first->id, $second->id]);
        $job->handle();
        $job->handle();
        $job->handle();

        $dispatchIds = [];
        Http::assertSent(function (Request $request) use ($first, $second, &$dispatchIds): bool {
            if ($request->url() !== 'https://sms.example.test/api/message/sms/send-batch') {
                return false;
            }
            $dispatchIds[] = $request['dispatch_id'];

            return $request->hasHeader('Content-Type', 'application/json')
                && $request['messages'] === [
                    ['user_sms_id' => (string) $first->id, 'to' => '998901234567', 'text' => 'Salom'],
                    ['user_sms_id' => (string) $second->id, 'to' => '998901234568', 'text' => 'Xabar'],
                ]
                && $request['from'] === '4546'
                && $request['callback_url'] === route('sms.eskiz.callback').'?token=callback-secret';
        });
        $this->assertCount(2, $dispatchIds);
        $this->assertSame($dispatchIds[0], $dispatchIds[1]);
        $this->assertSame('sending', $first->refresh()->status);
        $this->assertSame('dispatch-123', $first->gateway_batch_id);
        $this->assertNull($first->external_id);
    }

    public function test_send_response_does_not_overwrite_an_early_delivery_callback(): void
    {
        $sms = $this->makeSms();
        Http::fake([
            'sms.example.test/api/auth/login' => Http::response(['data' => ['token' => 'token']]),
            'sms.example.test/api/message/sms/send' => function () use ($sms) {
                $this->postJson(route('sms.eskiz.callback').'?token=callback-secret', [
                    'user_sms_id' => (string) $sms->id,
                    'message_id' => 'message-123',
                    'status' => 'DELIVERED',
                ])->assertOk();

                return Http::response(['id' => 'message-123']);
            },
        ]);

        (new SendSms($sms->id, $sms->phone, $sms->content))->handle();
        $this->assertSame('sent', $sms->refresh()->status);
        $this->assertSame('message-123', $sms->external_id);
    }

    public function test_callback_stays_closed_without_a_configured_token(): void
    {
        config(['sms.eskiz.callback_token' => null]);
        $this->postJson(route('sms.eskiz.callback'), ['status' => 'DELIVERED'])->assertServiceUnavailable();
    }

    public function test_callback_rejects_an_incorrect_token(): void
    {
        $this->postJson(route('sms.eskiz.callback').'?token=wrong', ['status' => 'DELIVERED'])->assertUnauthorized();
    }

    public function test_callback_updates_delivered_message_by_our_id(): void
    {
        $sms = $this->makeSms(['status' => 'sending']);
        $this->postJson(route('sms.eskiz.callback').'?token=callback-secret', [
            'user_sms_id' => (string) $sms->id,
            'message_id' => 'message-123',
            'status' => 'DELIVRD',
            'status_date' => '2026-01-01 10:00:00',
            'price' => 100,
            'parts_count' => 2,
        ])->assertOk();
        $this->assertSame('sent', $sms->refresh()->status);
        $this->assertSame('message-123', $sms->external_id);
        $this->assertSame(2, $sms->parts_count);
        $this->assertSame(200, $sms->total_price);
        $this->assertNull($sms->error_log);
        Http::assertNothingSent();
    }

    public function test_callback_falls_back_to_provider_id_and_ignores_intermediate_status(): void
    {
        $sms = $this->makeSms(['status' => 'sending', 'external_id' => 'message-123']);
        $this->postJson(route('sms.eskiz.callback').'?token=callback-secret', [
            'request_id' => 'message-123', 'status' => 'WAITING',
        ])->assertOk();
        $this->assertSame('sending', $sms->refresh()->status);
        $this->postJson(route('sms.eskiz.callback').'?token=callback-secret', [
            'request_id' => 'message-123', 'status' => 'UNDELIV',
        ])->assertOk();
        $this->assertSame('failed', $sms->refresh()->status);
        $this->assertSame('UNDELIV', $sms->error_log);
    }

    public function test_template_submission_and_status_check_use_own_eskiz_account(): void
    {
        Http::fake([
            'sms.example.test/api/auth/login' => Http::response(['data' => ['token' => 'token']]),
            'sms.example.test/api/user/template' => Http::response(['data' => ['id' => 25]]),
            'sms.example.test/api/user/templates' => Http::sequence()
                ->push(['result' => [['id' => 25, 'status' => 'service', 'original_text' => 'Salom {STUDENT}']]])
                ->push(['result' => [['id' => 25, 'status' => 'rejected', 'original_text' => 'Salom {STUDENT}']]]),
        ]);
        $template = SmsTemplate::create(['name' => 'Notice', 'content' => 'Salom {STUDENT}', 'status' => 'not_registered', 'type' => 'student']);
        $service = app(SmsTemplateService::class);
        $service->sendToModeration($template->id);
        $this->assertSame(SmsTemplate::STATUS_MODERATION, $template->refresh()->status);
        $this->assertSame('25', (string) $template->eskiz_template_id);
        Http::assertSent(fn (Request $request): bool => $request->url() === 'https://sms.example.test/api/user/template'
            && collect($request->data())->pluck('contents', 'name')->get('template') === $template->content);
        $service->checkStatus($template);
        $this->assertSame(SmsTemplate::STATUS_REGISTERED, $template->refresh()->status);
        $service->checkStatus($template);
        $this->assertSame(SmsTemplate::STATUS_NOT_REGISTERED, $template->refresh()->status);
        $service->deleteTemplate($template->id);
        $this->assertSoftDeleted($template);
        Http::assertSentCount(4);
    }

    private function makeSms(array $attributes = []): Sms
    {
        return Sms::create(array_merge([
            'phone' => '998901234567',
            'content' => 'Salom',
            'status' => 'pending',
            'character_count' => 5,
            'channel' => 'eskiz',
            'smsable_type' => User::class,
            'smsable_id' => User::factory()->create()->id,
        ], $attributes));
    }
}
