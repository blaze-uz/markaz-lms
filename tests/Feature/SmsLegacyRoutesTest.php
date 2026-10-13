<?php

namespace Tests\Feature;

use Tests\TestCase;

class SmsLegacyRoutesTest extends TestCase
{
    public function test_removed_sms_integrations_are_not_routed(): void
    {
        foreach ([
            '/api/sms/token/sync',
            '/api/v2/sms/token/sync',
            '/api/sms/callback',
            '/api/sms/callbacks/message',
            '/api/sms/callbacks/balance',
            '/api/sms/template/callback',
            '/api/sms/update-status',
            '/api/sms/delete-template',
            '/api/sms/payment',
            '/api/sms/balance/sync',
            '/api/sms-report',
            '/api/sync/default-sms-templates',
        ] as $uri) {
            $this->postJson($uri)->assertNotFound();
        }
    }
}
