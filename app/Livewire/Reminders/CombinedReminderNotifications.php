<?php

namespace App\Livewire\Reminders;

use App\Models\LeadReminder;
use App\Models\LeadReminderRead;
use App\Models\StudentReminder;
use App\Models\StudentReminderRead;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CombinedReminderNotifications extends Component
{
    public function markAllAsRead(): void
    {
        $userId = Auth::id();
        if ($userId === null) {
            return;
        }

        foreach ($this->dueUnreadLeadQuery()->get() as $reminder) {
            LeadReminderRead::query()->firstOrCreate([
                'lead_reminder_id' => $reminder->id,
                'user_id' => $userId,
            ]);
        }

        foreach ($this->dueUnreadStudentQuery()->get() as $reminder) {
            StudentReminderRead::query()->firstOrCreate([
                'student_reminder_id' => $reminder->id,
                'user_id' => $userId,
            ]);
        }
    }

    public function openLeadReminder(int $leadReminderId): void
    {
        $userId = Auth::id();
        if ($userId === null) {
            return;
        }

        $reminder = LeadReminder::query()
            ->with('lead:id,name')
            ->findOrFail($leadReminderId);

        if ($reminder->lead === null) {
            return;
        }

        LeadReminderRead::query()->firstOrCreate([
            'lead_reminder_id' => $reminder->id,
            'user_id' => $userId,
        ]);

        $this->redirect(route('admin.leads.inertia', [
            'search' => $reminder->lead->name ?? '',
        ]));
    }

    public function openStudentReminder(int $studentReminderId): void
    {
        $userId = Auth::id();
        if ($userId === null) {
            return;
        }

        $reminder = StudentReminder::query()
            ->with('student:id,name,surname')
            ->findOrFail($studentReminderId);

        if ($reminder->student === null) {
            return;
        }

        StudentReminderRead::query()->firstOrCreate([
            'student_reminder_id' => $reminder->id,
            'user_id' => $userId,
        ]);

        $this->redirect(route('admin.students.show', $reminder->student_id));
    }

    public function render(): View
    {
        $userId = Auth::id();

        $dueLeadReminders = $this->dueForBranchLeadQuery()
            ->with(['lead:id,name'])
            ->when($userId !== null, function (Builder $query) use ($userId) {
                $query->withExists([
                    'reads' => function (Builder $q) use ($userId) {
                        $q->where('user_id', $userId);
                    },
                ]);
            })
            ->orderByDesc('reminder_date')
            ->orderByDesc('reminder_time')
            ->limit(25)
            ->get();

        $dueStudentReminders = $this->dueForUserStudentQuery()
            ->with(['student:id,name,surname'])
            ->when($userId !== null, function (Builder $query) use ($userId) {
                $query->withExists([
                    'reads' => function (Builder $q) use ($userId) {
                        $q->where('user_id', $userId);
                    },
                ]);
            })
            ->orderByDesc('reminder_date')
            ->orderByDesc('reminder_time')
            ->limit(25)
            ->get();

        $leadUnread = $userId === null ? 0 : $this->dueUnreadLeadQuery()->count();
        $studentUnread = $userId === null ? 0 : $this->dueUnreadStudentQuery()->count();

        return view('livewire.reminders.combined-reminder-notifications', [
            'dueLeadReminders' => $dueLeadReminders,
            'dueStudentReminders' => $dueStudentReminders,
            'unreadCount' => $leadUnread + $studentUnread,
            'leadUnreadCount' => $leadUnread,
            'studentUnreadCount' => $studentUnread,
        ]);
    }

    private function dueForBranchLeadQuery(): Builder
    {
        $query = LeadReminder::query()
            ->due()
            ->notCompleted()
            ->whereHas('lead');

        if (Auth::id() === null) {
            return $query->whereRaw('0 = 1');
        }

        return $query;
    }

    private function dueUnreadLeadQuery(): Builder
    {
        $userId = Auth::id();

        $query = $this->dueForBranchLeadQuery();

        if ($userId === null) {
            return $query->whereRaw('0 = 1');
        }

        return $query->whereDoesntHave('reads', function (Builder $q) use ($userId) {
            $q->where('user_id', $userId);
        });
    }

    private function dueForUserStudentQuery(): Builder
    {
        $query = StudentReminder::query()
            ->due()
            ->notCompleted()
            ->where('assigned_user_id', Auth::id())
            ->whereHas('student');

        if (Auth::id() === null) {
            return $query->whereRaw('0 = 1');
        }

        return $query;
    }

    private function dueUnreadStudentQuery(): Builder
    {
        $userId = Auth::id();

        $query = $this->dueForUserStudentQuery();

        if ($userId === null) {
            return $query->whereRaw('0 = 1');
        }

        return $query->whereDoesntHave('reads', function (Builder $q) use ($userId) {
            $q->where('user_id', $userId);
        });
    }
}
