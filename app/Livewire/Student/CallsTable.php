<?php

namespace App\Livewire\Student;

use App\Models\Call;
use App\Models\Student;
use Livewire\Component;
use Livewire\WithPagination;

class CallsTable extends Component
{
    use WithPagination;

    public Student $student;

    // Form properties
    public $showAddForm = false;

    public $status = 'not_answered';

    public $description = '';

    public $phone = '';

    public $duration = '';

    protected $rules = [
        'status' => 'required|in:answered,not_answered,busy,no_answer',
        'description' => 'required|min:3',
        'phone' => 'nullable|string',
        'duration' => 'nullable|integer|min:0',
    ];

    protected $messages = [
        'status.required' => 'translation.calls.status_required',
        'status.in' => 'translation.calls.status_invalid',
        'description.required' => 'translation.calls.description_required',
        'description.min' => 'translation.calls.description_min',
        'duration.integer' => 'translation.calls.duration_integer',
        'duration.min' => 'translation.calls.duration_min',
    ];

    public function mount(Student $student)
    {
        $this->student = $student;
        $this->phone = $student->phone;
    }

    public function toggleAddForm()
    {
        $this->showAddForm = ! $this->showAddForm;
        if (! $this->showAddForm) {
            $this->resetForm();
        }
    }

    public function addCall()
    {
        abort_unless(auth()->user()?->can('student-edit'), 403);

        $this->validate();

        try {
            Call::create([
                'student_id' => $this->student->id,
                'user_id' => auth()->id(),
                'status' => $this->status,
                'description' => $this->description,
                'phone' => $this->phone ?: $this->student->phone,
                'duration' => $this->duration ?: null,
                'called_at' => now(),
            ]);

            $this->resetForm();
            $this->showAddForm = false;

            session()->flash('success', __('translation.calls.call_added_successfully'));
            $this->resetPage();
        } catch (\Exception $e) {
            session()->flash('error', __('translation.calls.error_adding_call'));
        }
    }

    public function deleteCall($callId)
    {
        abort_unless(auth()->user()?->can('student-edit'), 403);

        try {
            $call = Call::findOrFail($callId);

            // Check if the call belongs to this student
            if ($call->student_id !== $this->student->id) {
                session()->flash('error', __('translation.calls.call_not_found'));

                return;
            }

            $call->delete();

            session()->flash('success', __('translation.calls.call_deleted_successfully'));
            $this->resetPage();
        } catch (\Exception $e) {
            session()->flash('error', __('translation.calls.error_deleting_call'));
        }
    }

    public function resetForm()
    {
        $this->status = 'not_answered';
        $this->description = '';
        $this->phone = $this->student->phone;
        $this->duration = '';
        $this->resetValidation();
    }

    public function render()
    {
        $calls = $this->student->calls()
            ->with('user')
            ->latest('called_at')
            ->paginate(10);

        return view('livewire.student.calls-table', [
            'calls' => $calls,
        ]);
    }
}
