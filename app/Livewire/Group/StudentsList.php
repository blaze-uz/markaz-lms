<?php

namespace App\Livewire\Group;

use App\Models\Group;
use App\Models\GroupStudentLog;
use App\Models\Student;
use App\Models\StudentGroupPayment;
use App\Services\StudentCacheService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class StudentsList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search;

    public $group;

    public $group_id;

    public $filter = 'all';

    public $selectedStudents = [];

    public $selectedStudent;

    // Bulk detach properties
    public $studentsToDetach = [];

    public function mount($group_id): void
    {
        $this->group_id = $group_id;
        $this->group = Group::findOrFail($group_id);
    }

    public function sendSms()
    {
        $ids = $this->selectedStudents;
        $type = 'students';

        return redirect()->route('admin.sms.create', ['sendSmsIds' => json_encode($ids), 'sendSmsType' => $type]);
    }

    public function bulkDetach()
    {
        if (empty($this->selectedStudents)) {
            session()->flash('error_alert', __('translation.please_select_students'));

            return;
        }

        // Get students with their debt information
        $studentsWithDebts = Student::whereIn('id', $this->selectedStudents)
            ->with(['groupPayments' => function ($query) {
                $query->where('group_id', $this->group_id)
                    ->where('left_amount', '>', 0);
            }])
            ->get();

        // Store for modal display
        $this->studentsToDetach = $studentsWithDebts->toArray();

        // Check if any students have debts
        $hasDebts = $studentsWithDebts->filter(function ($student) {
            return $student->groupPayments->isNotEmpty();
        })->isNotEmpty();

        if ($hasDebts) {
            $this->dispatch('show-bulk-detach-modal');
        } else {
            $this->processBulkDetach();
        }
    }

    public function processBulkDetach()
    {
        abort_unless(auth()->user()?->can('group-detach-student'), 403);

        if (empty($this->selectedStudents)) {
            session()->flash('error_alert', __('translation.please_select_students'));

            return;
        }

        DB::beginTransaction();

        try {
            foreach ($this->selectedStudents as $studentId) {
                $student = Student::find($studentId);

                if (! $student) {
                    continue;
                }

                // Delete any remaining debts for this student in this group
                StudentGroupPayment::where('student_id', $studentId)
                    ->where('group_id', $this->group_id)
                    ->delete();

                // Detach the student from the group
                $student->groups()->detach($this->group_id);

                // Create a log entry
                GroupStudentLog::create([
                    'group_id' => $this->group_id,
                    'student_id' => $studentId,
                    'action' => 'detach',
                    'date' => now(),
                    'causer_id' => auth()->id(),
                ]);
            }

            DB::commit();

            $affectedStudents = Student::whereIn('id', $this->selectedStudents)->get();
            StudentCacheService::invalidateAfterStudentGroupMembershipChange($affectedStudents, $this->group);

            $detachedCount = count($this->selectedStudents);
            session()->flash('success_alert', __('translation.students_detached_successfully', ['count' => $detachedCount]));

            // Reset selections and close modal
            $this->selectedStudents = [];
            $this->dispatch('hide-bulk-detach-modal');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error_alert', __('translation.error_occurred').': '.$e->getMessage());
        }
    }

    public function selectAll(): void
    {
        $students = Student::whereRelation('groups', 'group_id', $this->group_id)
            ->when($this->search, function ($query) {
                $query->whereRaw("CONCAT(name, ' ', surname) ilike '%' || ? || '%'", [$this->search])
                    ->orWhere('phone', 'ilike', '%'.$this->search.'%')
                    ->orWhere('parent_phone', 'ilike', '%'.$this->search.'%');
            })
            ->when($this->filter !== 'all', function ($query) {
                $query->whereHas('groupPayments', function ($query) {
                    $query->where('left_amount', '>', 0);
                });
            })
            ->orderBy('name')
            ->limit(15)
            ->pluck('id')
            ->toArray();

        if (count($this->selectedStudents) === count($students)) {
            $this->selectedStudents = [];
        } else {
            $this->selectedStudents = $students;
        }
    }

    public function placeholder()
    {
        return view('livewire.placeholders.skeleton');
    }

    public function showInfo($student_id)
    {
        $this->reset('selectedStudent');
        $this->selectedStudent = Student::where('id', $student_id)->with(['account', 'groups'])->first();
        $this->selectedStudent->amount = $this->selectedStudent->groups->where('id', $this->group_id)->first()->pivot->amount;
        $this->selectedStudent->attached_at = $this->selectedStudent->groups->where('id', $this->group_id)->first()->pivot->attached_at;
        $this->selectedStudent->comment = $this->selectedStudent->groups->where('id', $this->group_id)->first()->pivot->comment;
        $this->dispatch('showInfo', $student_id);
    }

    public function render()
    {
        $relation = ($this->group->state !== 'completed') ? 'groups' : 'completedGroups';
        $students = Student::with(['account', 'groups'])->whereRelation($relation, 'group_id', $this->group_id)
            ->with(['groupPayments' => function ($query) {
                $query->where('group_id', $this->group_id);
            }, 'completedGroups'])
            ->when($this->search, function ($query) {
                $query->whereRaw("CONCAT(name, ' ', surname) ilike '%' || ? || '%'", [$this->search])
                    ->orWhere('phone', 'ilike', '%'.$this->search.'%')
                    ->orWhere('parent_phone', 'ilike', '%'.$this->search.'%');
            })
            ->when($this->filter !== 'all', function ($query) {
                $query->whereHas('groupPayments', function ($query) {
                    $query->where('left_amount', '>', 0);
                });
            })
            ->orderBy('name')
            ->paginate(15);

        return view('livewire.group.students-list', compact('students'));
    }
}
