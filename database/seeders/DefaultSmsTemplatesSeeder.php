<?php

namespace Database\Seeders;

use App\Models\SmsTemplate;
use Illuminate\Database\Seeder;

class DefaultSmsTemplatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Darsga qatnashmaganlik haqida',
                'content' => "Assalomu alaykum {STUDENT} bugungi dars mashg'ulotiga qatnashmaganliklari haqida ogohlantiramiz! {STUDY_CENTER}",
                'type' => 'student',
                'status' => config('sms.driver') === 'log' ? SmsTemplate::STATUS_REGISTERED : SmsTemplate::STATUS_NOT_REGISTERED,
                'sample' => "Assalomu alaykum Ali Valiyev bugungi dars mashg'ulotiga qatnashmaganliklari haqida ogohlantiramiz! {STUDY_CENTER}",
                'template' => "Assalomu alaykum {STUDENT} bugungi dars mashg'ulotiga qatnashmaganliklari haqida ogohlantiramiz! {STUDY_CENTER}",
            ],
            [
                'name' => 'Yangi yil tabrigi',
                'content' => '{STUDENT} sizni kirib kelayotgan 2025-yil bilan tabriklaymiz! {STUDY_CENTER}',
                'type' => 'student',
                'status' => config('sms.driver') === 'log' ? SmsTemplate::STATUS_REGISTERED : SmsTemplate::STATUS_NOT_REGISTERED,
                'sample' => 'Ali Valiyev sizni kirib kelayotgan 2025-yil bilan tabriklaymiz! {STUDY_CENTER}',
                'template' => '{STUDENT} sizni kirib kelayotgan 2025-yil bilan tabriklaymiz! {STUDY_CENTER}',
            ],
            [
                'name' => "Tug'ilgan kun tabrigi",
                'content' => "{STUDENT} sizni tug'ilgan kuniz bilan tabriklaymiz! {STUDY_CENTER}",
                'type' => 'student',
                'status' => config('sms.driver') === 'log' ? SmsTemplate::STATUS_REGISTERED : SmsTemplate::STATUS_NOT_REGISTERED,
                'sample' => "Ali Valiyev sizni tug'ilgan kuniz bilan tabriklaymiz! {STUDY_CENTER}",
                'template' => "{STUDENT} sizni tug'ilgan kuniz bilan tabriklaymiz! {STUDY_CENTER}",
            ],
        ];

        foreach ($templates as $template) {
            SmsTemplate::updateOrCreate(
                ['name' => $template['name']],
                $template
            );
        }
    }
}
