<?php

namespace App\Services;

use App\Exceptions\InvalidTelegramInitData;

/**
 * Checks the initData Telegram hands a Mini App, as described in
 * https://core.telegram.org/bots/webapps#validating-data-received-via-the-mini-app
 *
 * Every field except `hash` is sorted by key and joined as "key=value" lines;
 * `hash` must be the HMAC-SHA256 of those lines, keyed with
 * HMAC-SHA256(bot token, key "WebAppData"). Only Telegram and whoever holds the
 * bot token can produce it, so the `user` inside is who really opened the app.
 */
class TelegramInitDataVerifier
{
    /** Older initData is refused, so a leaked copy cannot be replayed for long. */
    public const MAX_AGE_SECONDS = 86400;

    /** Allowance for a server clock running behind Telegram's. */
    private const CLOCK_SKEW_SECONDS = 60;

    /**
     * @return array<string, mixed> the signed fields, with `user` decoded and `auth_date` as an int
     *
     * @throws InvalidTelegramInitData
     */
    public function verify(string $initData, string $botToken, int $maxAgeSeconds = self::MAX_AGE_SECONDS): array
    {
        if ($botToken === '') {
            throw new InvalidTelegramInitData('the bot token is not configured');
        }

        // Decoded the way Telegram's own clients encode it ("+" is a space).
        $fields = [];
        foreach (explode('&', $initData) as $pair) {
            if ($pair === '') {
                continue;
            }
            [$key, $value] = array_pad(explode('=', $pair, 2), 2, '');
            $fields[urldecode($key)] = urldecode($value);
        }

        $hash = $fields['hash'] ?? '';
        unset($fields['hash']);

        if ($hash === '' || $fields === []) {
            throw new InvalidTelegramInitData('initData is missing or unsigned');
        }

        ksort($fields, SORT_STRING);
        $dataCheckString = implode("\n", array_map(
            fn ($key, $value) => $key.'='.$value,
            array_keys($fields),
            $fields,
        ));
        $secretKey = hash_hmac('sha256', $botToken, 'WebAppData', true);

        if (! hash_equals(hash_hmac('sha256', $dataCheckString, $secretKey), $hash)) {
            throw new InvalidTelegramInitData('the signature does not match');
        }

        $authDate = filter_var($fields['auth_date'] ?? null, FILTER_VALIDATE_INT);
        $now = now()->getTimestamp();

        if ($authDate === false || $authDate > $now + self::CLOCK_SKEW_SECONDS || $now - $authDate > $maxAgeSeconds) {
            throw new InvalidTelegramInitData('auth_date is missing, in the future or too old');
        }

        $user = json_decode($fields['user'] ?? '', true);
        $id = is_array($user) ? ($user['id'] ?? null) : null;

        if (! (is_int($id) && $id > 0) && ! (is_string($id) && ctype_digit($id))) {
            throw new InvalidTelegramInitData('initData carries no Telegram user');
        }

        $fields['user'] = $user;
        $fields['auth_date'] = $authDate;

        return $fields;
    }
}
