<?php

namespace App\Livewire\Student;

use App\Models\Group;
use App\Models\GroupStudentLog;
use App\Models\StudentGroupPayment;
use App\Services\StudentCacheService;
use Livewire\Attributes\On;
use Livewire\Component;

class TransferGroup extends Component
{
    public $student;

    public $studentGroups;

    public $selectedStudentGroup;

    public $selectedStudentGroupDebts;

    public $selectedDebts = [];

    public $selectedTransferGroup;

    public $selectedTransferGroupId = '';

    public $transferGroup;

    public $transferGroupDebts;

    public function mount($student)
    {
        $this->student = $student;
        $this->studentGroups = $student->groups;
        $this->selectedStudentGroup = $this->studentGroups->first();
        if (! $this->selectedStudentGroup) {
            abort(404);
        }
        $this->selectedStudentGroupDebts = StudentGroupPayment::where('student_id', $student->id)
            ->where('group_id', $this->selectedStudentGroup->id)
            ->where('left_amount', '!=', 0)
            ->get();
    }

    public function selectDebt($debt)
    {
        if (in_array($debt, $this->selectedDebts)) {
            $this->selectedDebts = array_diff($this->selectedDebts, [$debt]);
        } else {
            $this->selectedDebts[] = $debt;
        }
    }

    #[On('changeStudentGroup')]
    public function changeStudentGroup($group_id): void
    {
        $this->selectedStudentGroup = Group::find($group_id);
        $this->selectedStudentGroupDebts = StudentGroupPayment::where('student_id', $this->student->id)
            ->where('group_id', $group_id)
            ->where('left_amount', '!=', 0)
            ->get();
    }

    #[On('changeTransferGroup')]
    public function changeTransferGroup($group_id): void
    {
        $this->selectedTransferGroup = Group::find($group_id);
        $this->transferGroupDebts = StudentGroupPayment::where('student_id', $this->student->id)
            ->where('group_id', $group_id)
            ->where('left_amount', '!=', 0)
            ->get();
    }

    public function render()
    {
        $groups = $this->getGroups();

        return view('livewire.student.transfer-group', [
            'groups' => $groups,
        ]);
    }

    private function getGroups()
    {
        return Group::orderBy('name')
            ->where('state', '!=', 'completed')
            ->where('id', '!=', $this->selectedStudentGroup->id)
            ->select(['id', 'name'])->get();
    }

    public function transfer()
    {
        $this->validate([
            'selectedTransferGroup' => 'required',
        ]);

        //delete selected debts
        StudentGroupPayment::whereIn('id', $this->selectedDebts)->delete();

        //detach group
        $this->student->groups()->detach($this->selectedStudentGroup->id);

        // Create a log entry
        GroupStudentLog::create([
            'group_id' => $this->selectedStudentGroup->id,
            'student_id' => $this->student->id,
            'action' => 'detach',
            'date' => now(),
            'causer_id' => auth()->id(),
        ]);

        //attach group
        $this->student->groups()->attach($this->selectedTransferGroup);

        // Create a log entry
        GroupStudentLog::create([
            'group_id' => $this->selectedTransferGroup->id,
            'student_id' => $this->student->id,
            'action' => 'attach',
            'date' => now(),
            'causer_id' => auth()->id(),
        ]);

        $this->addDebt();

        StudentCacheService::invalidateAfterStudentGroupMembershipChange(
            $this->student,
            [$this->selectedStudentGroup, $this->selectedTransferGroup]
        );

        session()->flash('success_alert', __('translation.alert_messages.success'));

        return redirect()->route('admin.students.show', $this->student->id);
    }

    private function addDebt(): void
    {
        if ($this->selectedTransferGroup->state !== 'active') {
            return;
        }

        $range = $this->getTransferDebtRange();

        if (! $range) {
            return;
        }

        StudentGroupPayment::create([
            'student_id' => $this->student->id,
            'group_id' => $this->selectedTransferGroup->id,
            'date' => now(),
            'amount' => (int) $this->selectedTransferGroup->price,
            'left_amount' => (int) $this->selectedTransferGroup->price,
            'range' => $range,
        ]);
    }

    private function getTransferDebtRange(): ?string
    {
        if ($this->selectedTransferGroup->payment_type === 'one_time') {
            $hasDebt = StudentGroupPayment::where('group_id', $this->selectedTransferGroup->id)
                ->where('student_id', $this->student->id)
                ->exists();

            if ($hasDebt) {
                return null;
            }

            return 'one_time/'.$this->student->id.'/'.now()->format('Y-m-d');
        }

        return StudentGroupPayment::where('group_id', $this->selectedTransferGroup->id)
            ->latest()
            ->first()?->range;
    }
}
