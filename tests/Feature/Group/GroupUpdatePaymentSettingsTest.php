<?php

namespace Tests\Feature\Group;

use App\Models\Group;
use App\Services\GroupService;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class GroupUpdatePaymentSettingsTest extends TestCase
{
    public function test_missing_payment_type_preserves_existing_one_time_settings(): void
    {
        $startDate = Carbon::parse('2026-03-27');
        $group = new Group([
            'payment_type' => 'one_time',
            'start_date' => $startDate,
            'lesson_count' => null,
            'duration_months' => 3,
        ]);

        $data = [
            'payment_type' => null,
            'start_date' => null,
            'lesson_count' => null,
        ];

        $result = (new GroupService(new Group()))->updatePaymentSettings($data, $group);

        $this->assertSame('one_time', $result['payment_type']);
        $this->assertTrue($startDate->equalTo($result['start_date']));
        $this->assertNull($result['lesson_count']);
        $this->assertSame(3, $result['duration_months']);
    }
}
