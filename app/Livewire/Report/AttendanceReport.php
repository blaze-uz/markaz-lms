<?php

namespace App\Livewire\Report;

use App\Models\Attendance;
use App\Models\Student;
use App\Models\Group;
use App\Models\User;
use App\Enums\AttendanceStatus;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class AttendanceReport extends Component
{
    use WithPagination;

    public $searchQuery = '';
    public $date = '';
    public $selectedGroup = '';
    public $selectedTeacher = '';
    public $teacherSearch = '';
    public $groupSearch = '';
    public $paginationTheme = 'bootstrap';

    protected $queryString = [
        'searchQuery' => ['except' => ''],
        'date' => ['except' => ''],
        'selectedGroup' => ['except' => ''],
        'selectedTeacher' => ['except' => ''],
    ];

    public function mount()
    {
        $this->date = Carbon::now()->format('Y.m.d') . " - " . Carbon::now()->format('Y.m.d');
    }

    public function updatedSearchQuery()
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

    public function updatedTeacherSearch()
    {
        // Reset page when searching
    }

    public function updatedGroupSearch()
    {
        // Reset page when searching
    }

    public function clearFilters()
    {
        $this->searchQuery = '';
        $this->selectedGroup = '';
        $this->selectedTeacher = '';
        $this->teacherSearch = '';
        $this->groupSearch = '';
        $this->resetPage();

        // Emit custom event to clear Select2
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
                DB::raw("COUNT(CASE WHEN attendances.status = '" . AttendanceStatus::ABSENT->value . "' THEN 1 END) as absent_count"),
                DB::raw("COUNT(CASE WHEN attendances.status = '" . AttendanceStatus::PRESENT->value . "' THEN 1 END) as present_count"),
                DB::raw('COUNT(attendances.id) as total_attendances')
            ])
            ->join('attendances', 'students.id', '=', 'attendances.student_id')
            ->join('groups', 'attendances.group_id', '=', 'groups.id')
            ->whereBetween('attendances.date', [$startDate, $endDate])
            ->when($this->searchQuery, function ($q) {
                $q->where(function ($q) {
                    $q->where('students.name', 'ilike', '%' . $this->searchQuery . '%')
                        ->orWhere('students.surname', 'ilike', '%' . $this->searchQuery . '%')
                        ->orWhere('students.phone', 'ilike', '%' . $this->searchQuery . '%')
                        ->orWhere('groups.name', 'ilike', '%' . $this->searchQuery . '%');
                });
            })
            ->when($this->selectedGroup, function ($q) {
                $q->where('groups.id', $this->selectedGroup);
            })
            ->when($this->selectedTeacher, function ($q) {
                $q->where('groups.teacher_id', $this->selectedTeacher);
            })
            ->groupBy('students.id', 'students.name', 'students.surname', 'students.phone', 'students.parent_phone', 'groups.name')
            ->orderByDesc('absent_count')
            ->paginate(15);
    }

    public function getStatsProperty()
    {
        [$startDate, $endDate] = $this->getDateRange();

        $stats = DB::table('attendances')
            ->join('students', 'attendances.student_id', '=', 'students.id')
            ->join('groups', 'attendances.group_id', '=', 'groups.id')
            ->whereBetween('attendances.date', [$startDate, $endDate])
            ->when($this->selectedGroup, function ($q) {
                $q->where('groups.id', $this->selectedGroup);
            })
            ->when($this->selectedTeacher, function ($q) {
                $q->where('groups.teacher_id', $this->selectedTeacher);
            })
            ->selectRaw("
                COUNT(CASE WHEN attendances.status = '" . AttendanceStatus::PRESENT->value . "' THEN 1 END) as total_present,
                COUNT(CASE WHEN attendances.status = '" . AttendanceStatus::ABSENT->value . "' THEN 1 END) as total_absent,
                COUNT(attendances.id) as total_records,
                COUNT(DISTINCT students.id) as total_students
            ")
            ->first();

        return [
            'total_present' => $stats->total_present ?? 0,
            'total_absent' => $stats->total_absent ?? 0,
            'total_records' => $stats->total_records ?? 0,
            'total_students' => $stats->total_students ?? 0,
            'attendance_rate' => $stats->total_records > 0 ? round(($stats->total_present / $stats->total_records) * 100, 1) : 0
        ];
    }

    public function getGroupsProperty()
    {
        return Group::active()
            ->with('teacher')
            ->when($this->groupSearch, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'ilike', '%' . $this->groupSearch . '%')
                        ->orWhereHas('teacher', function ($tq) {
                            $tq->where('name', 'ilike', '%' . $this->groupSearch . '%');
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
                $query->where('name', 'ilike', '%' . $this->teacherSearch . '%');
            })
            ->orderBy('name')
            ->get();
    }

    public function render()
    {
        return view('livewire.report.attendance-report', [
            'students' => $this->students,
            'stats' => $this->stats,
            'groups' => $this->groups,
            'teachers' => $this->teachers
        ]);
    }

    private function getDateRange()
    {
        $dates = explode(" - ", $this->date);
        $startDate = Carbon::parse(str_replace('.', '-', $dates[0]))->startOfDay();
        $endDate = Carbon::parse(str_replace('.', '-', $dates[1] ?? $dates[0]))->endOfDay();

        return [$startDate, $endDate];
    }
}
