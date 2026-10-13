<?php

namespace App\Livewire\Reports;

use App\Exports\DebtsExport;
use App\Models\Group;
use App\Models\StudentGroupPayment;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class DebtsReport extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';

    public $selectedRange = '';

    public $selectedGroup = null;

    public $selectedTeacher = null;

    public $perPage = 10;

    public $groups = [];

    public $teachers = [];

    public $ranges = [];

    public $page = 1;

    public $showDeleteModal = false;

    public $debtToDelete = null;

    // Summary statistics
    public $totalDebts = 0;

    public $activeDebts = 0;

    public $deletedStudentDebts = 0;

    public $totalAmount = 0;

    public $activeAmount = 0;

    public $deletedAmount = 0;

    protected $queryString = [
        'search' => ['except' => ''],
        'selectedRange' => ['except' => ''],
        'selectedGroup' => ['except' => null],
        'selectedTeacher' => ['except' => null],
        'perPage' => ['except' => 10],
        'page' => ['except' => 1],
    ];

    protected function getBaseQuery()
    {
        return StudentGroupPayment::query()
            ->select([
                'student_group_payments.*',
                'students.name',
                'students.surname',
                'students.phone',
                'groups.name as group_name',
            ])
            ->join('students', 'student_group_payments.student_id', '=', 'students.id')
            ->join('groups', 'student_group_payments.group_id', '=', 'groups.id')
            ->join('branch_group', 'groups.id', '=', 'branch_group.group_id')
            ->where('student_group_payments.left_amount', '>', 0)
            ->where('branch_group.branch_id', auth()->user()->current_branch_id)
            ->when($this->search, function ($q) {
                $term = '%'.$this->search.'%';
                $q->where(function ($q) use ($term) {
                    $q->where(DB::raw("CONCAT(students.name, ' ', students.surname)"), 'ilike', $term)
                        ->orWhere(DB::raw("CONCAT(students.surname, ' ', students.name)"), 'ilike', $term)
                        ->orWhere('students.name', 'ilike', $term)
                        ->orWhere('students.surname', 'ilike', $term)
                        ->orWhere('students.phone', 'ilike', $term)
                        ->orWhere('groups.name', 'ilike', $term);
                });
            })
            ->when($this->selectedGroup, function ($q) {
                $q->where('student_group_payments.group_id', $this->selectedGroup);
            })
            ->when($this->selectedTeacher, function ($q) {
                $q->where('groups.teacher_id', $this->selectedTeacher);
            })
            ->when($this->selectedRange, function ($q) {
                $q->where('student_group_payments.range', $this->selectedRange);
            });
    }

    protected function loadSummaryStatistics()
    {
        $query = $this->getBaseQuery()
            ->select([
                DB::raw('COUNT(*) as total_debts'),
                DB::raw('SUM(left_amount) as total_amount'),
                DB::raw('SUM(CASE WHEN students.deleted_at IS NULL THEN 1 ELSE 0 END) as active_debts'),
                DB::raw('SUM(CASE WHEN students.deleted_at IS NULL THEN left_amount ELSE 0 END) as active_amount'),
                DB::raw('SUM(CASE WHEN students.deleted_at IS NOT NULL OR students.id IS NULL THEN 1 ELSE 0 END) as deleted_debts'),
                DB::raw('SUM(CASE WHEN students.deleted_at IS NOT NULL OR students.id IS NULL THEN left_amount ELSE 0 END) as deleted_amount'),
            ]);

        $stats = $query->first();

        $this->totalDebts = $stats->total_debts ?? 0;
        $this->activeDebts = $stats->active_debts ?? 0;
        $this->deletedStudentDebts = $stats->deleted_debts ?? 0;
        $this->totalAmount = $stats->total_amount ?? 0;
        $this->activeAmount = $stats->active_amount ?? 0;
        $this->deletedAmount = $stats->deleted_amount ?? 0;
    }

    public function mount()
    {
        $this->groups = Group::select('id', 'name', 'teacher_id')
            ->with('teacher:id,name')
            ->get();

        $this->teachers = $this->getTeachers();
        $this->ranges = StudentGroupPayment::whereNotNull('range')
            ->distinct()
            ->orderByRaw('range DESC')
            ->pluck('range');
        $this->loadSummaryStatistics();
    }

    public function updated($field)
    {
        if (in_array($field, ['search', 'selectedGroup', 'selectedTeacher', 'selectedRange'])) {
            $this->loadSummaryStatistics();
        }
    }

    #[On('search-updated')]
    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectedGroup()
    {
        $this->resetPage();
    }

    public function updatedSelectedTeacher()
    {
        $this->resetPage();
    }

    public function updatedSelectedRange()
    {
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->reset(['search', 'selectedGroup', 'selectedTeacher', 'selectedRange', 'perPage']);
        $this->resetPage();
        $this->loadSummaryStatistics();
    }

    public function confirmDelete($debtId)
    {
        $this->debtToDelete = StudentGroupPayment::with(['student' => function ($q) {
            $q->withTrashed();
        }])->findOrFail($debtId);
        $this->dispatch('showDeleteModal');
    }

    public function cancelDelete()
    {
        $this->debtToDelete = null;
        $this->dispatch('hideDeleteModal');
    }

    public function deleteDebt()
    {
        abort_unless(auth()->user()?->can('group-update-debt'), 403);

        if ($this->debtToDelete && (! $this->debtToDelete->student || $this->debtToDelete->student->trashed())) {
            $this->debtToDelete->delete();
            session()->flash('success', __('translation.alert_messages.deleted'));
            $this->dispatch('hideDeleteModal');
            $this->loadSummaryStatistics();
        }
        $this->debtToDelete = null;
    }

    public function getDebts()
    {
        return $this->getBaseQuery()
            ->with(['student' => function ($q) {
                $q->select('id', 'name', 'surname', 'phone', 'deleted_at');
            }, 'group' => function ($q) {
                $q->select('id', 'name');
            }])
            ->orderBy('student_group_payments.created_at', 'desc')
            ->paginate($this->perPage);
    }

    public function export($type)
    {
        $filename = 'debts_report_'.now()->format('Y_m_d_H_i_s').'.'.$type;

        return Excel::download(new DebtsExport(
            search: $this->search,
            groupId: $this->selectedGroup,
            range: $this->selectedRange,
            teacherId: $this->selectedTeacher
        ), $filename);
    }

    public function calculateDebts(): void
    {
        $this->dispatch('calculating-started', message: __('translation.calculating_started_please_wait'));
        Artisan::call('app:daily-review-command');
        $this->loadSummaryStatistics();
    }

    private function getNumericValue($value)
    {
        if (empty($value)) {
            return null;
        }

        return (float) str_replace([',', ' ', 'UZS'], '', $value);
    }

    public function render()
    {
        $debts = $this->getDebts();
        $totalDebt = $debts->sum('left_amount');

        return view('livewire.reports.debts-report', [
            'debts' => $debts,
            'totalDebt' => $totalDebt,
            'totalDebts' => $this->totalDebts,
            'activeDebts' => $this->activeDebts,
            'deletedStudentDebts' => $this->deletedStudentDebts,
            'totalAmount' => $this->totalAmount,
            'activeAmount' => $this->activeAmount,
            'deletedAmount' => $this->deletedAmount,
        ]);
    }

    public function getTeachers()
    {
        return User::whereHas('roles', function ($query) {
            $query->where('name', 'Teacher');
        })
            ->whereHas('branches', function ($query) {
                $query->where('branch_id', auth()->user()->current_branch_id);
            })
            ->get(['id', 'name'])
            ->map(function ($teacher) {
                return [
                    'id' => $teacher->id,
                    'name' => $teacher->name,
                ];
            });
    }
}
