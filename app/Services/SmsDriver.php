<?php

namespace App\Services;

use App\Models\Sms;
use App\Models\SmsTemplate;
use Illuminate\Support\Collection;

interface SmsDriver
{
    public function send(Sms $sms, string $phone, string $content): array;

    public function bulk(Collection $sms, string $dispatchId): array;

    public function submitTemplate(SmsTemplate $template): void;

    public function checkTemplate(SmsTemplate $template): void;
}
