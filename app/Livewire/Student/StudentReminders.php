<?php

namespace App\Livewire\Student;

use App\Models\Student;
use App\Models\StudentReminder;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Component;

class StudentReminders extends Component
{
    public Student $student;

    public bool $showForm = false;

    public string $reminder_date = '';

    public string $reminder_time = '';

    public string $reminder_note = '';

    public ?int $assigned_user_id = null;

    public function mount(Student $student): void
    {
        $this->student = $student;
        $this->reminder_date = now()->format('Y-m-d');
        $this->reminder_time = now()->format('H:i');
    }

    public function toggleForm(): void
    {
        $this->showForm = ! $this->showForm;
        if (! $this->showForm) {
            $this->resetValidation();
        }
    }

    public function save(): void
    {
        abort_unless(auth()->user()?->can('student-edit'), 403);

        $assignableIds = $this->assignableStaffIds();

        $this->validate([
            'reminder_date' => ['required', 'date'],
            'reminder_time' => ['required', 'date_format:H:i'],
            'reminder_note' => ['nullable', 'string', 'max:5000'],
            'assigned_user_id' => ['required', 'integer', Rule::in($assignableIds)],
        ], [], [
            'reminder_date' => __('translation.students.reminder_date'),
            'reminder_time' => __('translation.students.reminder_time'),
            'reminder_note' => __('translation.students.reminder_note'),
            'assigned_user_id' => __('translation.students.reminder_assignee'),
        ]);

        StudentReminder::query()->create([
            'student_id' => $this->student->id,
            'assigned_user_id' => $this->assigned_user_id,
            'reminder_date' => $this->reminder_date,
            'reminder_time' => $this->reminder_time.':00',
            'reminder_note' => $this->reminder_note ?: null,
            'reminder_status' => 'pending',
            'created_by' => auth()->id(),
        ]);

        $this->reminder_note = '';
        $this->showForm = false;
        session()->flash('success_alert', __('translation.students.reminder_created'));
    }

    public function markCompleted(int $reminderId): void
    {
        abort_unless(auth()->user()?->can('student-edit'), 403);

        $reminder = StudentReminder::query()
            ->where('student_id', $this->student->id)
            ->findOrFail($reminderId);

        $reminder->update(['reminder_status' => 'completed']);
        session()->flash('success_alert', __('translation.students.reminder_completed'));
    }

    public function delete(int $reminderId): void
    {
        abort_unless(auth()->user()?->can('student-edit'), 403);

        $reminder = StudentReminder::query()
            ->where('student_id', $this->student->id)
            ->findOrFail($reminderId);

        $reminder->delete();
        session()->flash('success_alert', __('translation.students.reminder_deleted'));
    }

    /**
     * @return Collection<int, User>
     */
    private function assignableStaff(): Collection
    {
        return User::query()
            ->permission('student-edit')
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    /**
     * @return array<int, int>
     */
    private function assignableStaffIds(): array
    {
        return $this->assignableStaff()->pluck('id')->all();
    }

    public function render(): View
    {
        $reminders = StudentReminder::query()
            ->where('student_id', $this->student->id)
            ->with(['assignedUser:id,name', 'causer:id,name'])
            ->orderByDesc('reminder_date')
            ->orderByDesc('reminder_time')
            ->get();

        return view('livewire.student.student-reminders', [
            'reminders' => $reminders,
            'staffUsers' => $this->assignableStaff(),
        ]);
    }
}
