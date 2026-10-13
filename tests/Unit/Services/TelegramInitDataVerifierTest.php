<?php

namespace Tests\Unit\Services;

use App\Exceptions\InvalidTelegramInitData;
use App\Services\TelegramInitDataVerifier;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * The staff mini-app now trusts only initData Telegram signed with the staff
 * bot's token, so this check is the whole of its authentication.
 */
class TelegramInitDataVerifierTest extends TestCase
{
    private const TOKEN = '123456:staff-bot-token';

    private TelegramInitDataVerifier $verifier;

    protected function setUp(): void
    {
        parent::setUp();

        $this->verifier = new TelegramInitDataVerifier();
        $this->travelTo(Carbon::createFromTimestamp(1_790_000_000));
    }

    /**
     * Known answers from aiogram's test suite, signed independently of this
     * code — they pin the key order of the two HMACs and "+" read as a space.
     */
    public function test_it_accepts_initdata_signed_elsewhere_by_the_spec(): void
    {
        $this->travelTo(Carbon::createFromTimestamp(1650385342 + 60));

        $first = $this->verifier->verify(
            'auth_date=1650385342&user=%7B%22id%22%3A42%2C%22first_name%22%3A%22Test%22%7D&query_id=test'
            .'&hash=46d2ea5e32911ec8d30999b56247654460c0d20949b6277af519e76271182803',
            '42:TEST',
        );
        $this->assertSame(42, $first['user']['id']);

        $second = $this->verifier->verify(
            'auth_date=1650385342&user=%7B%22id%22%3A+%22123456789%22%2C+%22first_name%22%3A+%22PlaceholderFirstName%22%2C+%22last_name%22%3A+%22PlaceholderLastName+%5Cud83c%5Cuddfa%5Cud83c%5Cudde6%22%2C+%22username%22%3A+%22Latand%22%2C+%22language_code%22%3A+%22en%22%2C+%22is_premium%22%3A+%22true%22%2C+%22allows_write_to_pm%22%3A+%22true%22%7D&query_id=test'
            .'&hash=b3c8b293f14ad0f7f0abcf769aea1209a72295d30a87eb0e74df855d32e53bfe',
            '42:TEST',
        );
        $this->assertSame('123456789', $second['user']['id']);
    }

    public function test_a_valid_signature_passes_and_yields_the_telegram_user(): void
    {
        $data = $this->verifier->verify($this->sign(['user' => $this->user(700001)]), self::TOKEN);

        $this->assertSame(700001, $data['user']['id']);
        $this->assertSame(now()->getTimestamp(), $data['auth_date']);
    }

    public function test_the_bot_api_8_signature_field_is_part_of_what_is_signed(): void
    {
        $data = $this->verifier->verify(
            $this->sign(['user' => $this->user(700001), 'signature' => 'ed25519-signature']),
            self::TOKEN,
        );

        $this->assertSame(700001, $data['user']['id']);
    }

    public function test_missing_initdata_is_rejected(): void
    {
        $this->assertRejected('');
    }

    public function test_initdata_without_a_hash_is_rejected(): void
    {
        $this->assertRejected('auth_date='.now()->getTimestamp().'&user='.rawurlencode($this->user(700001)));
    }

    public function test_initdata_signed_with_another_bot_token_is_rejected(): void
    {
        $this->assertRejected($this->sign(['user' => $this->user(700001)], 'another:bot-token'));
    }

    public function test_a_user_swapped_into_signed_initdata_is_rejected(): void
    {
        $signed = $this->sign(['user' => $this->user(700001)]);

        $this->assertRejected(str_replace(rawurlencode($this->user(700001)), rawurlencode($this->user(700002)), $signed));
    }

    public function test_a_second_user_appended_to_signed_initdata_is_rejected(): void
    {
        $this->assertRejected($this->sign(['user' => $this->user(700001)]).'&user='.rawurlencode($this->user(700002)));
    }

    public function test_stale_initdata_is_rejected(): void
    {
        $this->assertRejected($this->sign([
            'user' => $this->user(700001),
            'auth_date' => now()->getTimestamp() - TelegramInitDataVerifier::MAX_AGE_SECONDS - 1,
        ]));
    }

    public function test_initdata_dated_in_the_future_is_rejected(): void
    {
        $this->assertRejected($this->sign([
            'user' => $this->user(700001),
            'auth_date' => now()->getTimestamp() + 3600,
        ]));
    }

    public function test_signed_initdata_without_a_user_is_rejected(): void
    {
        $this->assertRejected($this->sign(['query_id' => 'AAH']));
    }

    public function test_nothing_passes_while_the_bot_token_is_not_configured(): void
    {
        $this->assertRejected($this->sign(['user' => $this->user(700001)], ''), '');
    }

    private function assertRejected(string $initData, string $token = self::TOKEN): void
    {
        try {
            $this->verifier->verify($initData, $token);
            $this->fail('Expected the initData to be rejected.');
        } catch (InvalidTelegramInitData) {
            $this->addToAssertionCount(1);
        }
    }

    private function user(int $id): string
    {
        return json_encode(['id' => $id, 'first_name' => 'Ustoz', 'language_code' => 'uz']);
    }

    /**
     * Signs fields the way Telegram does (auth_date defaults to now).
     */
    private function sign(array $fields, string $token = self::TOKEN): string
    {
        $fields += ['auth_date' => now()->getTimestamp(), 'query_id' => 'AAHdF6IQAAAAAN0XohDhrOrc'];
        ksort($fields);

        $dataCheckString = implode("\n", array_map(fn ($k, $v) => "{$k}={$v}", array_keys($fields), $fields));
        $fields['hash'] = hash_hmac('sha256', $dataCheckString, hash_hmac('sha256', $token, 'WebAppData', true));

        return http_build_query($fields, '', '&', PHP_QUERY_RFC3986);
    }
}
