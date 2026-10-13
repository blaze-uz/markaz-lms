<?php

namespace App\Services;

use App\Models\Group;
use App\Models\Settings;
use App\Models\User;
use DefStudio\Telegraph\Models\TelegraphBot;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class StaffTelegramBotService
{
    public function getUser(?string $telegram_id = null)
    {
        if ($telegram_id) {
            return User::where('telegram_id', $telegram_id)->first();
        }

        return auth()->user();
    }

    public function getUserByTelegramId(string $telegram_id)
    {
        // First try the old approach - direct telegram_id column in users table
        $user = User::where('telegram_id', $telegram_id)->first();
        if ($user) {
            $this->ensureUserBranchContext($user);

            return $user;
        }

        // Then try the new approach - through user_telegrams table using chat_id
        $userTelegram = \App\Models\UserTelegram::where('chat_id', $telegram_id)
            ->where('status', 'active')
            ->with('user')
            ->first();

        if ($userTelegram && $userTelegram->user) {
            $this->ensureUserBranchContext($userTelegram->user);

            return $userTelegram->user;
        }

        // This is a staff bot, so we don't look for students
        return null;
    }

    public function ensureUserBranchContext(User $user): void
    {
        // Get user's branches
        $userBranches = $user->branches;

        if ($userBranches->isEmpty()) {
            return; // User has no branches assigned
        }

        // Check if user has a current branch set and if it's valid
        $currentBranch = $user->currentBranch;
        if (! $currentBranch || ! $userBranches->contains($currentBranch->id)) {
            // Set to first available branch
            $firstBranch = $userBranches->first();
            $user->update(['current_branch_id' => $firstBranch->id]);
        }
    }

    public function getTodayGroups($user)
    {
        $today = strtolower(now()->format('l'));

        // Ensure user has branch context
        $this->ensureUserBranchContext($user);

        // Optimized query to fetch today's lessons with necessary relationships
        // Scope to user's current branch
        return $user->groups()
            ->where('state', 'active')
            ->whereHas('branches', function ($query) use ($user) {
                $query->where('branch_id', $user->current_branch_id);
            })
            ->whereHas('groupTimes', function ($query) use ($today) {
                $query->where('name', $today);
            })
            ->withCount('students')
            ->with(['groupTimes' => function ($query) use ($today) {
                $query->where('name', $today)->orderBy('start');
            }])
            ->get()
            ->map(function ($group) {
                return [
                    'id' => $group->id,
                    'name' => $group->name,
                    'start_time' => $group->groupTimes->first()?->start,
                    'students_count' => $group->students_count,
                    'attendance_exists' => $group->attendances()->where('date', now()->format('Y-m-d'))->exists(),
                ];
            })
            ->sortBy('start_time')
            ->values();
    }

    /**
     * One of the staff member's own groups — the only ones the mini-app lists.
     */
    public function getUserGroup(User $user, $id): ?Group
    {
        return $user->groups()->whereKey($id)->first();
    }

    /**
     * The staff bot's token: Telegram signs the mini-app's initData with it.
     * The settings page saves it both on the Telegraph bot that sends the
     * "open app" button and in settings.
     */
    public function botToken(): ?string
    {
        return TelegraphBot::where('name', 'Staff app')->value('token')
            ?: Settings::where('key', 'staff_telegram_bot_token')->value('value');
    }

    public function getGroups($user)
    {
        // Ensure user has branch context
        $this->ensureUserBranchContext($user);

        return $user->groups()
            ->whereHas('branches', function ($query) use ($user) {
                $query->where('branch_id', $user->current_branch_id);
            })
            ->get()
            ->map(function ($group) {
                return [
                    'id' => $group->id,
                    'name' => $group->name,
                    'students_count' => $group->students->count() ?? 0,
                    'state' => $group->state,
                ];
            });
    }

    public function getGroupStudents($id)
    {
        $group = Group::find($id);

        $students = $group->students()
            ->orderBy('name')
            ->orderBy('surname')
            ->get()
            ->map(function ($student) {
                return [
                    'id' => $student->id,
                    'name' => $student->name,
                    'surname' => $student->surname,
                    'phone' => $student->getFormattedPhone(),
                    'balance' => $student->balance ?? 0,
                ];
            });

        return $students;
    }

    public function checkAttendanceExists($id)
    {
        $group = Group::find($id);

        return $group->attendances()->where('date', now()->format('Y-m-d'))->exists();
    }

    public function login($data)
    {
        $credentials = [
            'email' => $data['login'],
            'password' => $data['password'],
        ];

        if (Auth::attempt($credentials)) {
            session()->regenerate();

            return true;
        }

        throw ValidationException::withMessages([
            'login' => [__('auth.failed')],
        ]);
    }

    public function updateToken(array $data): void
    {
        // Delete existing bot if exists
        TelegraphBot::where('name', 'Staff app')->delete();

        try {
            // Create new bot
            $bot = TelegraphBot::create([
                'token' => $data['bot_token'],
                'name' => 'Staff app',
            ]);

            // Register webhook (domain is configured in config/telegraph.php)
            $bot->registerWebhook()->send();

            // Save token in settings
            Settings::updateOrCreate(
                ['key' => 'staff_telegram_bot_token'],
                ['value' => $data['bot_token']]
            );
        } catch (\Exception $e) {
            throw ValidationException::withMessages([
                'bot_token' => [__('Invalid bot token or webhook registration failed')],
            ]);
        }
    }

    public function getGroupTimes($id)
    {
        $group = Group::find($id);
        $today = now()->format('l'); // Get current day name

        return $group->groupTimes()
            ->where('name', $today)
            ->first()?->start;
    }

    public function getGroupAttendances($id, $date = null)
    {
        $group = Group::find($id);

        if (! $group) {
            return collect([]);
        }

        $query = $group->attendances()->with('student');

        if ($date) {
            $query->whereDate('date', $date);
        } else {
            // Default to today if no date provided
            $query->whereDate('date', now()->format('Y-m-d'));
        }

        return $query->orderBy('date', 'desc')
            ->orderBy('student_id')
            ->get()
            ->map(function ($attendance) {
                return [
                    'id' => $attendance->id,
                    'student_id' => $attendance->student_id,
                    'student_name' => $attendance->student ? ($attendance->student->name . ' ' . $attendance->student->surname) : 'N/A',
                    'status' => $attendance->status,
                    'comment' => $attendance->comment,
                    'score' => $attendance->score,
                    'homework' => $attendance->homework ?? false,
                    'date' => $attendance->date->format('Y-m-d'),
                    'formatted_date' => $attendance->date->format('d.m.Y'),
                ];
            });
    }

    public function getGroupAttendanceDates($id)
    {
        $group = Group::find($id);

        if (! $group) {
            return [];
        }

        return $group->attendances()
            ->selectRaw('DATE(date) as attendance_date')
            ->distinct()
            ->orderBy('attendance_date', 'desc')
            ->pluck('attendance_date')
            ->map(function ($date) {
                return [
                    'value' => $date,
                    'label' => \Carbon\Carbon::parse($date)->format('d.m.Y'),
                ];
            })
            ->toArray();
    }
}
