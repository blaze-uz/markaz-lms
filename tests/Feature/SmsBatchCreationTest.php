<?php

namespace Tests\Feature;

use App\Models\SmsTemplate;
use App\Services\SmsBatchSnapshotService;
use Tests\TestCase;

class SmsBatchCreationTest extends TestCase
{
    public function test_it_builds_sms_batch_snapshot_for_students_and_parents(): void
    {
        $snapshotService = new SmsBatchSnapshotService();

        $template = new SmsTemplate([
            'name' => 'Payment reminder',
            'content' => 'Salom {STUDENT}, {STUDY_CENTER}: {PHONE}',
            'eskiz_status' => 'service',
        ]);

        $snapshot = $snapshotService->build(
            receivers: collect([
                [
                    'id' => 15,
                    'name' => 'Ali',
                    'surname' => 'Valiyev',
                    'phone' => '971234567',
                    'parent_phone' => '931234567',
                ],
            ]),
            template: $template,
            sendSmsType: 'student',
            sendToStudents: true,
            sendToParents: true,
            scheduledAt: null,
            smsGroupId: 10,
            branchId: 20,
            createdById: 30,
            status: 'sending',
            source: 'manual',
            studyCenterName: 'Markaz LMS',
            studyCenterPhone: '901112233',
        );

        $this->assertSame(1, $snapshot['selected_receivers_count']);
        $this->assertSame(2, $snapshot['recipient_count']);
        $this->assertSame(2, $snapshot['sms_parts_count']);
        $this->assertSame('students_and_parents', $snapshot['recipient_scope']);
        $this->assertSame('gsm7', $snapshot['encoding']);
        $this->assertSame('service', $snapshot['sms_type']);
        $this->assertSame(40, $snapshot['template_character_count']);
        $this->assertSame(1, $snapshot['min_parts_per_message']);
        $this->assertSame(1, $snapshot['max_parts_per_message']);
        $this->assertSame(1.0, $snapshot['avg_parts_per_message']);
        $this->assertSame([
            'mobiuz' => 1,
            'ucell' => 1,
        ], $snapshot['meta']['operator_counts']);
    }
}
