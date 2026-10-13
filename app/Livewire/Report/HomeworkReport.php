<?php

namespace App\Livewire\Report;

use App\Models\Attendance;
use App\Models\Group;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class HomeworkReport extends Component
{
    use WithPagination;

    public $searchQuery = '';

    public $date = '';

    public $selectedGroup = '';

    public $selectedTeacher = '';

    public $teacherSearch = '';

    public $groupSearch = '';

    public $paginationTheme = 'bootstrap';

    public ?int $lockTeacherId = null;

    protected $queryString = [
        'searchQuery' => ['except' => ''],
        'date' => ['except' => ''],
        'selectedGroup' => ['except' => ''],
        'selectedTeacher' => ['except' => ''],
    ];

    public function mount($startDate = null, $endDate = null): void
    {
        if ($startDate && $endDate) {
            $this->date = Carbon::parse($startDate)->format('Y.m.d').' - '.Carbon::parse($endDate)->format('Y.m.d');
        } else {
            $this->date = Carbon::now()->subDays(30)->format('Y.m.d').' - '.Carbon::now()->format('Y.m.d');
        }

        if ($this->lockTeacherId !== null) {
            $this->selectedTeacher = (string) $this->lockTeacherId;
        }
    }

    public function updatedSearchQuery()
    {
        $this->resetPage();
    }

    public function updatedSelectedGroup()
    {
        $this->resetPage();
    }

    public function updatedSelectedTeacher(): void
    {
        if ($this->lockTeacherId !== null) {
            $this->selectedTeacher = (string) $this->lockTeacherId;

            return;
        }

        if ($this->selectedGroup !== '' && $this->selectedGroup !== null) {
            $groupBelongsToTeacher = Group::query()
                ->active()
                ->whereKey($this->selectedGroup)
                ->when($this->effectiveTeacherId(), fn ($q) => $q->where('teacher_id', $this->effectiveTeacherId()))
                ->exists();
            if (! $groupBelongsToTeacher) {
                $this->selectedGroup = '';
            }
        }
        $this->resetPage();
    }

    public function updatedTeacherSearch()
    {
        // Reset page when searching
    }

    public function updatedGroupSearch()
    {
        // Reset page when searching
    }

    public function clearFilters(): void
    {
        $this->searchQuery = '';
        $this->selectedGroup = '';
        if ($this->lockTeacherId === null) {
            $this->selectedTeacher = '';
        } else {
            $this->selectedTeacher = (string) $this->lockTeacherId;
        }
        $this->teacherSearch = '';
        $this->groupSearch = '';
        $this->resetPage();

        $this->js('window.dispatchEvent(new CustomEvent("filtersCleared"))');
    }

    public function getStudentsProperty()
    {
        [$startDate, $endDate] = $this->getDateRange();

        return Student::query()
            ->select([
                'students.id',
                'students.name',
                'students.surname',
                'students.phone',
                'students.parent_phone',
                'groups.name as group_name',
                DB::raw('COUNT(CASE WHEN attendances.homework = true THEN 1 END) as completed_count'),
                DB::raw('COUNT(CASE WHEN attendances.homework = false THEN 1 END) as not_completed_count'),
                DB::raw('COUNT(attendances.id) as total_homework'),
            ])
            ->join('attendances', 'students.id', '=', 'attendances.student_id')
            ->join('groups', 'attendances.group_id', '=', 'groups.id')
            ->whereBetween('attendances.date', [$startDate, $endDate])
            ->whereNotNull('attendances.homework')
            ->whereHas('branches', function ($query) {
                $query->where('branch_id', auth()->user()->current_branch_id);
            })
            ->when($this->searchQuery, function ($q) {
                $q->where(function ($q) {
                    $q->where('students.name', 'ilike', '%'.$this->searchQuery.'%')
                        ->orWhere('students.surname', 'ilike', '%'.$this->searchQuery.'%')
                        ->orWhere('students.phone', 'ilike', '%'.$this->searchQuery.'%')
                        ->orWhere('groups.name', 'ilike', '%'.$this->searchQuery.'%');
                });
            })
            ->when($this->selectedGroup, function ($q) {
                $q->where('groups.id', $this->selectedGroup);
            })
            ->when($this->effectiveTeacherId(), function ($q) {
                $q->where('groups.teacher_id', $this->effectiveTeacherId());
            })
            ->groupBy('students.id', 'students.name', 'students.surname', 'students.phone', 'students.parent_phone', 'groups.name')
            ->orderByDesc('not_completed_count')
            ->orderByDesc('total_homework')
            ->paginate(15);
    }

    public function getStatsProperty()
    {
        [$startDate, $endDate] = $this->getDateRange();

        $query = Attendance::query()
            ->whereBetween('date', [$startDate, $endDate])
            ->whereNotNull('homework')
            ->whereRelation('student', function ($query) {
                $query->whereHas('branches', function ($query) {
                    $query->where('branch_id', auth()->user()->current_branch_id);
                });
            })
            ->when($this->selectedGroup, function ($q) {
                $q->where('group_id', $this->selectedGroup);
            })
            ->when($this->effectiveTeacherId(), function ($q) {
                $q->whereHas('group', function ($gq) {
                    $gq->where('teacher_id', $this->effectiveTeacherId());
                });
            });

        $totalCompleted = (clone $query)->where('homework', true)->count();
        $totalNotCompleted = (clone $query)->where('homework', false)->count();
        $totalRecords = $query->count();
        $totalStudents = (clone $query)->distinct('student_id')->count('student_id');
        $completionRate = $totalRecords > 0 ? round(($totalCompleted / $totalRecords) * 100, 1) : 0;

        return [
            'total_completed' => $totalCompleted,
            'total_not_completed' => $totalNotCompleted,
            'total_records' => $totalRecords,
            'total_students' => $totalStudents,
            'completion_rate' => $completionRate,
        ];
    }

    public function getGroupsProperty()
    {
        return Group::active()
            ->with('teacher')
            ->when($this->effectiveTeacherId(), fn ($q) => $q->where('teacher_id', $this->effectiveTeacherId()))
            ->when($this->groupSearch, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'ilike', '%'.$this->groupSearch.'%')
                        ->orWhereHas('teacher', function ($tq) {
                            $tq->where('name', 'ilike', '%'.$this->groupSearch.'%');
                        });
                });
            })
            ->orderBy('name')
            ->get();
    }

    public function getTeachersProperty()
    {
        return User::whereHas('groups', function ($query) {
            $query->where('state', 'active');
        })
            ->when($this->teacherSearch, function ($query) {
                $query->where('name', 'ilike', '%'.$this->teacherSearch.'%');
            })
            ->orderBy('name')
            ->get();
    }

    public function render()
    {
        return view('livewire.report.homework-report', [
            'students' => $this->students,
            'stats' => $this->stats,
            'groups' => $this->groups,
            'teachers' => $this->teachers,
        ]);
    }

    private function effectiveTeacherId(): ?string
    {
        if ($this->lockTeacherId !== null) {
            return (string) $this->lockTeacherId;
        }

        if ($this->selectedTeacher === '' || $this->selectedTeacher === null) {
            return null;
        }

        return (string) $this->selectedTeacher;
    }

    private function getDateRange(): array
    {
        $dates = explode(' - ', $this->date);
        $startDate = Carbon::parse(str_replace('.', '-', $dates[0]))->startOfDay();
        $endDate = Carbon::parse(str_replace('.', '-', $dates[1] ?? $dates[0]))->endOfDay();

        return [$startDate, $endDate];
    }
}
