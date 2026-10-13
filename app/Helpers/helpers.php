<?php

use App\Models\Settings;

if (!function_exists('auth_id')) {
    function auth_id(): int|null
    {
        return auth()->id() ?? null;
    }
}

if (!function_exists("success_message")) {
    function success_message($message): void
    {
        session()->flash("success_alert", $message);
    }
}

if (!function_exists("error_message")) {
    function error_message($message): void
    {
        session()->flash("error_alert", $message);
    }
}

if (!function_exists('getLocales')) {
    function getLocales(): array
    {
        return [
            'uz' => [
                "name" => __("translation.uzbek"),
                "img" => asset("assets/images/flags/uz.svg")
            ],
            //            'en' => [
            //                "name" => __("translation.english"),
            //                "img" => asset("assets/images/flags/us.svg")
            //            ],
            //            'ru' => [
            //                "name" => __("translation.russian"),
            //                "img" => asset("assets/images/flags/russia.svg")
            //            ],
            //            'uz_RU' => [
            //                "name" => __("translation.cyrillic"),
            //                "img" => asset("assets/images/flags/uz.svg")
            //            ],
        ];
    }
}

if (!function_exists('add3dots')) {
    function add3dots($string, $repl, $limit)
    {
        if (strlen($string) > $limit)
            return substr($string, 0, $limit) . $repl;
        else
            return $string;
    }
}

if (!function_exists('money')) {
    function money($summa, $decimal = 0): string
    {
        return number_format($summa, $decimal, '.', ' ') . ' UZS';
    }
}

if (!function_exists("dateFormat")) {
    function dateFormat($date, $format = "d.m.Y", $locale = null): string
    {
        if (is_null($locale)) {
            $locale = "uz_Latn";
        }

        \Carbon\Carbon::setLocale($locale);

        try {
            $carbon_date = now();
            if (is_int($date)) {
                $carbon_date = \Carbon\Carbon::createFromTimestamp($date);
            } else {
                $carbon_date = \Carbon\Carbon::parse($date);
            }
            return $carbon_date->translatedFormat($format);
        } catch (\Exception $e) {
            return "Invalid date format";
        }
    }
}

if (!function_exists("readableNumber")) {
    function readableNumber($n = 0): int|string
    {
        if ($n >= 0 && $n < 1000) {
            // 1 - 999
            $n_format = floor($n);
            $suffix = '';
        } elseif ($n >= 1000 && $n < 1000000) {
            // 1k-999k
            $n_format = floor($n / 1000);
            $suffix = 'K+';
        } elseif ($n >= 1000000 && $n < 1000000000) {
            // 1m-999m
            $n_format = floor($n / 1000000);
            $suffix = 'M+';
        } elseif ($n >= 1000000000 && $n < 1000000000000) {
            // 1b-999b
            $n_format = floor($n / 1000000000);
            $suffix = 'B+';
        } elseif ($n >= 1000000000000) {
            // 1t+
            $n_format = floor($n / 1000000000000);
            $suffix = 'T+';
        }

        return (!empty($n_format . $suffix) ? $n_format . "$suffix" : 0);
    }
}

if (!function_exists('removeComma')) {
    function removeComma($number): string|null
    {
        if (is_null($number)) return null;
        return str_replace(',', '', $number);
    }
}

if (!function_exists('formattedPhone')) {
    function formattedPhone($phone): string|null
    {
        if (is_null($phone)) return null;

        if (strlen($phone) == 9) {
            $phone = "+998" . $phone;
        }
        return $phone;
    }
}

if (!function_exists('unformattedPhone')) {
    function unformattedPhone($phone): string|null
    {
        if (is_null($phone)) return null;
        if (strlen($phone) > 9) {
            $phone = substr($phone, 4);
        }
        return preg_replace('/[^0-9]/', '', $phone);
    }
}

if (!function_exists('debtRange')) {
    function debtRange($range): string
    {
        $parts = explode("/", $range);
        $start = dateFormat($parts[0], 'd-F');
        $end = dateFormat($parts[1], 'd-F');
        return $start . " / " . $end;
    }
}

if (!function_exists('isDate')) {
    function isDate($dateString): bool
    {
        $dateTime = DateTime::createFromFormat('Y-m-d', $dateString);
        return $dateTime && $dateTime->format('Y-m-d') === $dateString;
    }
}

if (!function_exists('settings')) {
    function settings($key, $default = null)
    {
        return cache()->remember('settings.' . $key, 600, function () use ($key, $default) {
            return Settings::where('key', $key)->value('value') ?? $default;
        });
    }
}

if (!function_exists('settingsFromDb')) {
    /**
     * Get settings directly from database without cache
     * Use this in jobs for multi-tenant environment to avoid cache conflicts
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function settingsFromDb($key, $default = null)
    {
        return Settings::where('key', $key)->value('value') ?? $default;
    }
}

if (!function_exists('isRateLimited')) {
    /**
     * Check if a rate limit is active
     *
     * @param string $key Rate limit key (e.g., 'sms-api-limit')
     * @return bool|int False if not limited, or seconds remaining if limited
     */
    function isRateLimited(string $key): bool|int
    {
        return \App\Models\RateLimit::isLimited($key);
    }
}

if (!function_exists('setRateLimit')) {
    /**
     * Set a rate limit
     *
     * @param string $key Rate limit key
     * @param int $seconds Duration in seconds
     * @return void
     */
    function setRateLimit(string $key, int $seconds): void
    {
        \App\Models\RateLimit::setLimit($key, $seconds);
    }
}
