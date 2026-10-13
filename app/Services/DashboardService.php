<?php

namespace App\Services;

use App\Models\Group;
use App\Models\GroupTime;
use App\Models\Room;
use App\Models\Settings;
use App\Models\Sms;
use App\Models\SmsTemplate;
use App\Models\Student;

class DashboardService
{
    public function getCalendarData($start, $end): array
    {
        if ($start && $end) {
            //start and end is this week day number like 1, 2, 3 change it to date
            $start = date('Y-m-d', strtotime('monday this week +'.($start - 1).' days'));
            $end = date('Y-m-d', strtotime('monday this week +'.($end - 1).' days'));
        }
        $startDate = $start ?? now()->format('Y-m-d');
        $endDate = $end ?? now()->format('Y-m-d');

        $colors = [
            '#6A9C89',
            '#1AACAC',
            '#8294C4',
            '#5C5470',
            '#1AACAC',
            '#DFA67B',
            '#654E92',
            '#F44336',
            '#E91E63',
            '#9C27B0',
            '#673AB7',
            '#3F51B5',
            '#2196F3',
            '#03A9F4',
            '#00BCD4',
            '#009688',
            '#4CAF50',
        ];

        $events = [];

        // Loop through the days in the specified date range
        $currentDate = $startDate;
        while ($currentDate <= $endDate) {
            // Determine the day of the week (e.g., Monday, Tuesday) based on the date
            $dayOfWeek = date('l', strtotime($currentDate));

            // Retrieve the data for the specific day of the week
            $groups = Group::all();
            $data = GroupTime::with('group.teacher')->whereIn('group_id', $groups->pluck('id')->toArray())
                ->where('name', strtolower($dayOfWeek)) // 'name' contains the day of the week
                ->get();

            if ($data) {
                foreach ($data as $item) {
                    $events[] = [
                        'id' => rand(1, 100),
                        'resourceId' => $item->room_id,
                        'start' => "$currentDate $item->start:00",
                        'end' => "$currentDate $item->end:00",
                        'title' => $item->group?->name ?? '-'.' - '.$item->group?->teacher->name ?? '-',
                        'description' => $item->start.' - '.$item->end,
                        'color' => $colors[rand(0, 15)],
                        'textColor' => '#ffffff',
                    ];
                }
            }

            // Move to the next day
            $currentDate = date('Y-m-d', strtotime($currentDate.' +1 day'));
        }

        return $events;
    }

    public function getResources(): array
    {
        $rooms = Room::all();
        $resources = [];
        foreach ($rooms as $room) {
            $resources[] = [
                'id' => $room->id,
                'title' => $room->name,
            ];
        }

        return $resources;
    }

    public function setTemplate($data)
    {
        $birthday_template = Settings::where('key', 'birthday_sms_template')->first();

        if ($birthday_template) {
            $birthday_template->update(['value' => $data['template_id']]);
        } else {
            Settings::create(['key' => 'birthday_sms_template', 'value' => $data['template_id']]);
        }
    }

    public function getBirthdaySmsTemplateExists()
    {
        return Settings::where('key', 'birthday_sms_template')->exists();
    }

    public function sendBirthdaySms()
    {
        $birthday_sms_template_id = Settings::where('key', 'birthday_sms_template')->first()?->value ?? null;
        if (! $birthday_sms_template_id) {
            return;
        }

        $template = SmsTemplate::find($birthday_sms_template_id);
        if (! $template) {
            return;
        }

        $students = Student::whereNotNull('birthday')
            ->whereMonth('birthday', now()->month)
            ->whereDay('birthday', now()->day)
            ->get();

        $studyCenter = Settings::where('key', 'name')->first();
        $phone = Settings::where('key', 'phone')->first();
        $birthdaySmsIds = [];

        foreach ($students as $receiver) {
            $receiverPhone = unformattedPhone($receiver->phone);
            $message = str_replace(
                ['{STUDY_CENTER}', '{STUDENT}', '{TEACHER}', '{PHONE}'],
                [
                    $studyCenter->value,
                    $receiver->getFullName(),
                    '',
                    formattedPhone($phone->value),
                ],
                $template->content
            );

            $sms = Sms::create([
                'phone' => '998'.$receiverPhone,
                'content' => $message,
                'status' => 'pending',
                'smsable_id' => $receiver->id,
                'smsable_type' => Student::class,
                'character_count' => strlen($message),
                'sms_template_id' => $birthday_sms_template_id,
            ]);

            $sms->branches()->attach(auth()->user()->current_branch_id);

            $birthdaySmsIds[] = $sms->id;
        }

        if ($birthdaySmsIds !== []) {
            SmsBulkDispatchService::dispatchFromSmsIds($birthdaySmsIds);
        }

        cache(['send_birthdays_sms'.now()->format('Y-m-d') => true], now()->endOfDay());
    }
}
