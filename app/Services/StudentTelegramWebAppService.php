<?php

namespace App\Services;

use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\GroupTime;
use App\Models\Settings;
use App\Models\Student;
use DefStudio\Telegraph\Models\TelegraphBot;
use DefStudio\Telegraph\Models\TelegraphChat;

class StudentTelegramWebAppService
{
    public function updateToken(array $data): void
    {
        // Delete existing bot if exists
        TelegraphBot::where('name', 'Students app')->delete();

        // Create new bot
        $bot = TelegraphBot::create([
            'token' => $data['bot_token'],
            'name' => 'Students app',
        ]);

        // Register webhook
        $bot->registerWebhook()->send();

        // Save token in settings
        Settings::updateOrCreate(
            ['key' => 'student_telegram_bot_token'],
            ['value' => $data['bot_token']]
        );
    }

    public function getToken()
    {
        return Settings::where('key', 'student_telegram_bot_token')->first();
    }

    /**
     * The student bot's token: Telegram signs the mini-app's initData with it.
     * The settings page saves it both on the Telegraph bot that sends the
     * "open app" button and in settings.
     */
    public function botToken(): ?string
    {
        return TelegraphBot::where('name', 'Students app')->value('token')
            ?: Settings::where('key', 'student_telegram_bot_token')->value('value');
    }

    public function getStudentByTelegramId(string $telegramId)
    {
        return Student::where('telegram_chat_id', $telegramId)->first();
    }

    /**
     * The student's chat with the student bot (the same Telegram id may also
     * chat with the staff bot).
     */
    public function getTelegraphChat(string $telegramId): ?TelegraphChat
    {
        return TelegraphChat::where('chat_id', $telegramId)
            ->whereHas('bot', fn ($query) => $query->where('name', 'Students app'))
            ->first();
    }

    public function getStudentGroups(Student $student)
    {
        $weekdayName = strtolower(now()->format('l'));

        return $student->groups()
            ->with(['teacher', 'course', 'groupTimes' => function ($query) use ($weekdayName) {
                $query->where('name', $weekdayName)
                    ->orderBy('start');
            }])
            ->get()
            ->map(function ($group) {
                $time = $group->groupTimes->first();
                $group->start_time = $time ? substr($time->start, 0, 5) : null;
                $group->end_time = $time ? substr($time->end, 0, 5) : null;

                return $group;
            });
    }

    public function getTodaysGroupTime(Student $student)
    {
        $weekdayName = strtolower(now()->format('l'));
        $currentTime = now()->format('H:i');

        $groupTime = GroupTime::query()
            ->whereIn('group_id', $student->groups->pluck('id')->toArray())
            ->where('name', $weekdayName)
            ->where('end', '>=', $currentTime)
            ->orderBy('start')
            ->with('group')
            ->first();

        if ($groupTime) {
            $groupTime->start = substr($groupTime->start, 0, 5);
            $groupTime->end = substr($groupTime->end, 0, 5);
        }

        return $groupTime;
    }

    public function getAttendancePercentage(Student $student)
    {
        $totalAttendances = Attendance::query()
            ->where('student_id', $student->id)
            ->count();

        if ($totalAttendances === 0) {
            return 0;
        }

        $presentCount = Attendance::query()
            ->where('student_id', $student->id)
            ->where('status', AttendanceStatus::PRESENT)
            ->count();

        return round(($presentCount / $totalAttendances) * 100);
    }

    private function getUzbekMonth($monthNumber)
    {
        $months = [
            1 => 'Yanvar',
            2 => 'Fevral',
            3 => 'Mart',
            4 => 'Aprel',
            5 => 'May',
            6 => 'Iyun',
            7 => 'Iyul',
            8 => 'Avgust',
            9 => 'Sentabr',
            10 => 'Oktabr',
            11 => 'Noyabr',
            12 => 'Dekabr',
        ];

        return $months[(int) $monthNumber];
    }

    public function getAttendanceHeatmapData(int $studentId, int $groupId, string $period = '6_months'): array
    {
        $now = now();
        $query = Attendance::query()
            ->where('student_id', $studentId)
            ->where('group_id', $groupId);

        // Set date range based on period
        switch ($period) {
            case 'this_month':
                $startDate = $now->startOfMonth();
                $endDate = $now->copy()->endOfMonth();
                break;
            case 'last_month':
                $startDate = $now->copy()->subMonth()->startOfMonth();
                $endDate = $now->copy()->subMonth()->endOfMonth();
                break;
            case '6_months':
            default:
                $startDate = $now->copy()->subMonths(5)->startOfMonth();
                $endDate = $now->copy()->endOfMonth();
                break;
        }

        $attendances = $query
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date')
            ->get();

        $result = [];

        // For monthly view, group by day
        if (in_array($period, ['this_month', 'last_month'])) {
            foreach ($attendances as $attendance) {
                $date = $attendance->date->format('d.m.Y');
                if (! isset($result[$date])) {
                    $result[$date] = [
                        'present' => 0,
                        'total' => 0,
                        'percentage' => 0,
                        'homework' => null,
                        'score' => null,
                        'status' => null,
                    ];
                }

                $result[$date]['total']++;
                if ($attendance->status === AttendanceStatus::PRESENT) {
                    $result[$date]['present']++;
                }
                $result[$date]['homework'] = $attendance->homework;
                $result[$date]['score'] = $attendance->score;
                $result[$date]['status'] = $attendance->status?->value;
            }

            // Calculate daily percentages
            foreach ($result as $date => $data) {
                $result[$date]['percentage'] = round(($data['present'] / $data['total']) * 100);
            }

            // Sort by date ascending
            ksort($result);
        }
        // For 6 months view, group by month
        else {
            // Initialize all months first
            $currentDate = $startDate->copy();
            while ($currentDate <= $endDate) {
                $monthKey = $this->getUzbekMonth($currentDate->format('n')) . ' ' . $currentDate->format('Y');
                $result[$monthKey] = [
                    'present' => 0,
                    'total' => 0,
                    'percentage' => 0,
                    'sort_key' => $currentDate->format('Y-m'), // Add sort key
                ];
                $currentDate->addMonth();
            }

            foreach ($attendances as $attendance) {
                $monthKey = $this->getUzbekMonth($attendance->date->format('n')) . ' ' . $attendance->date->format('Y');
                $result[$monthKey]['total']++;
                if ($attendance->status === AttendanceStatus::PRESENT) {
                    $result[$monthKey]['present']++;
                }
            }

            // Calculate monthly percentages
            foreach ($result as $month => $data) {
                if ($data['total'] > 0) {
                    $result[$month]['percentage'] = round(($data['present'] / $data['total']) * 100);
                }
            }

            // Sort by date ascending using the sort_key
            uasort($result, function ($a, $b) {
                return $a['sort_key'] <=> $b['sort_key'];
            });

            // Remove sort keys from final result
            foreach ($result as &$data) {
                unset($data['sort_key']);
            }
        }

        return $result;
    }
}
