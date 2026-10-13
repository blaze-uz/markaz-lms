<?php

namespace App\Traits;

use App\Models\Settings;

trait Telegram
{
    public function send($method, $data = [])
    {
        $telegram_bot_token = Settings::where('key', 'telegram_bot_token')->first()->value;
        $url = "https://api.telegram.org/bot" . $telegram_bot_token . "/" . $method;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $res = curl_exec($ch);
        return json_decode($res);
    }
}