<?php

namespace App\Services;

use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\Group;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class EducationalReportService
{
    public function getCardsData(?string $startDate, ?string $endDate): array
    {
        [$start, $end] = $this->normalizeDateRange($startDate, $endDate);
        $branchId = auth()->user()->current_branch_id;

        $attendancePercentage = $this->getAttendancePercentage($start, $end, $branchId);
        $studentsCount = Student::whereHas('branches', fn ($q) => $q->where('branch_id', $branchId))->count();
        $groupsCount = Group::active()->whereHas('branches', fn ($q) => $q->where('branch_id', $branchId))->count();
        $teachersCount = User::role('Teacher')->count();
        $deletedStudentsCount = Student::onlyTrashed()
            ->whereHas('branches', fn ($q) => $q->where('branch_id', $branchId))
            ->count();

        return [
            'attendance_percentage' => $attendancePercentage,
            'students_count' => $studentsCount,
            'groups_count' => $groupsCount,
            'teachers_count' => $teachersCount,
            'deleted_students_count' => $deletedStudentsCount,
        ];
    }

    public function getAttendanceChartData(?string $startDate, ?string $endDate): array
    {
        [$start, $end] = $this->normalizeDateRange($startDate, $endDate);
        $branchId = auth()->user()->current_branch_id;

        $attendances = Attendance::query()
            ->selectRaw('attendances.date, attendances.status, count(attendances.student_id) as count')
            ->join('students', 'attendances.student_id', '=', 'students.id')
            ->join('branch_student', 'students.id', '=', 'branch_student.student_id')
            ->where('branch_student.branch_id', $branchId)
            ->whereBetween('attendances.date', [$start, $end])
            ->whereNull('attendances.deleted_at')
            ->groupBy('attendances.date', 'attendances.status')
            ->get();

        $dates = [];
        $current = Carbon::parse($start);
        while ($current->lte(Carbon::parse($end))) {
            $key = $current->format('Y-m-d');
            $dates[$key] = ['present' => 0, 'absent' => 0];
            $current->addDay();
        }

        foreach ($attendances as $attendance) {
            $key = $attendance->date->format('Y-m-d');
            if (isset($dates[$key])) {
                $dates[$key][$attendance->status->value] = $attendance->count;
            }
        }

        $labels = array_keys($dates);
        $present = array_column($dates, 'present');
        $absent = array_column($dates, 'absent');

        return [
            'labels' => $labels,
            'series' => ['present' => $present, 'absent' => $absent],
        ];
    }

    /**
     * @return array{summary: array{present: int, absent: int, total: int, percentage: int}, records: \Illuminate\Contracts\Pagination\LengthAwarePaginator}
     */
    public function getAttendanceDetail(
        ?string $startDate,
        ?string $endDate,
        string $search = '',
        ?int $groupId = null,
        ?int $teacherId = null,
        int $perPage = 25,
        int $page = 1
    ): array {
        [$start, $end] = $this->normalizeDateRange($startDate, $endDate);
        $branchId = auth()->user()->current_branch_id;

        $baseQuery = Attendance::query()
            ->whereBetween('attendances.date', [$start, $end])
            ->whereNull('attendances.deleted_at')
            ->whereRelation(
                'student',
                fn ($q) => $q->whereHas('branches', fn ($bq) => $bq->where('branch_id', $branchId))
            )
            ->when($groupId !== null && $groupId > 0, fn ($q) => $q->where('attendances.group_id', $groupId))
            ->when($teacherId !== null && $teacherId > 0, fn ($q) => $q->whereRelation('group', 'teacher_id', $teacherId))
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($outer) use ($search) {
                    $outer->whereHas('student', function ($sq) use ($search) {
                        $sq->where('name', 'ilike', "%{$search}%")
                            ->orWhere('surname', 'ilike', "%{$search}%")
                            ->orWhere('phone', 'ilike', "%{$search}%");
                    })->orWhereHas('group', fn ($gq) => $gq->where('name', 'ilike', "%{$search}%"));
                });
            });

        $present = (clone $baseQuery)->where('status', AttendanceStatus::PRESENT)->count();
        $absent = (clone $baseQuery)->where('status', AttendanceStatus::ABSENT)->count();
        $total = $present + $absent;
        $percentage = $total > 0 ? (int) round(($present / $total) * 100) : 0;

        $presentValue = AttendanceStatus::PRESENT->value;
        $absentValue = AttendanceStatus::ABSENT->value;

        $paginator = (clone $baseQuery)
            ->join('students', 'attendances.student_id', '=', 'students.id')
            ->join('groups', 'attendances.group_id', '=', 'groups.id')
            ->select('attendances.student_id', 'attendances.group_id')
            ->selectRaw('MAX(students.name) as name')
            ->selectRaw('MAX(students.surname) as surname')
            ->selectRaw('MAX(students.phone) as student_phone')
            ->selectRaw('MAX(groups.name) as group_name')
            ->selectRaw(
                'SUM(CASE WHEN attendances.status = ? THEN 1 ELSE 0 END) as present_count',
                [$presentValue]
            )
            ->selectRaw(
                'SUM(CASE WHEN attendances.status = ? THEN 1 ELSE 0 END) as absent_count',
                [$absentValue]
            )
            ->selectRaw('COUNT(attendances.id) as total_count')
            ->groupBy('attendances.student_id', 'attendances.group_id')
            ->orderByRaw('absent_count DESC')
            ->orderByRaw('total_count DESC')
            ->paginate($perPage, ['*'], 'page', $page)
            ->withQueryString();

        $paginator->setCollection(
            $paginator->getCollection()->map(function ($row) {
                $presentCount = (int) $row->present_count;
                $absentCount = (int) $row->absent_count;
                $totalCount = (int) $row->total_count;
                $rate = $totalCount > 0 ? (int) round(($presentCount / $totalCount) * 100) : 0;

                return [
                    'student_id' => (int) $row->student_id,
                    'group_id' => (int) $row->group_id,
                    'student_name' => trim(($row->name ?? '').' '.($row->surname ?? '')),
                    'student_phone' => $row->student_phone,
                    'group_name' => $row->group_name ?? '-',
                    'present_count' => $presentCount,
                    'absent_count' => $absentCount,
                    'total_count' => $totalCount,
                    'rate' => $rate,
                ];
            })
        );

        return [
            'summary' => [
                'present' => $present,
                'absent' => $absent,
                'total' => $total,
                'percentage' => $percentage,
            ],
            'records' => $paginator,
        ];
    }

    public function getHomeworkChartData(?string $startDate, ?string $endDate): ?array
    {
        [$start, $end] = $this->normalizeDateRange($startDate, $endDate);
        $branchId = auth()->user()->current_branch_id;

        $hasData = Attendance::query()
            ->join('students', 'attendances.student_id', '=', 'students.id')
            ->join('branch_student', 'students.id', '=', 'branch_student.student_id')
            ->where('branch_student.branch_id', $branchId)
            ->whereBetween('attendances.date', [$start, $end])
            ->whereNotNull('attendances.homework')
            ->where('attendances.homework', true)
            ->whereNull('attendances.deleted_at')
            ->exists();

        if (! $hasData) {
            return null;
        }

        $attendances = Attendance::query()
            ->selectRaw('attendances.date, attendances.homework, count(attendances.student_id) as count')
            ->join('students', 'attendances.student_id', '=', 'students.id')
            ->join('branch_student', 'students.id', '=', 'branch_student.student_id')
            ->where('branch_student.branch_id', $branchId)
            ->whereBetween('attendances.date', [$start, $end])
            ->whereNotNull('attendances.homework')
            ->whereNull('attendances.deleted_at')
            ->groupBy('attendances.date', 'attendances.homework')
            ->get();

        $dates = [];
        $current = Carbon::parse($start);
        while ($current->lte(Carbon::parse($end))) {
            $key = $current->format('Y-m-d');
            $dates[$key] = ['completed' => 0, 'not_completed' => 0];
            $current->addDay();
        }

        foreach ($attendances as $attendance) {
            $key = $attendance->date->format('Y-m-d');
            if (isset($dates[$key])) {
                $bucket = $attendance->homework ? 'completed' : 'not_completed';
                $dates[$key][$bucket] = $attendance->count;
            }
        }

        return [
            'labels' => array_keys($dates),
            'series' => [
                'completed' => array_column($dates, 'completed'),
                'not_completed' => array_column($dates, 'not_completed'),
            ],
        ];
    }

    public function getStudentsChartData(?string $startDate, ?string $endDate): array
    {
        [$start, $end] = $this->normalizeDateRange($startDate, $endDate);

        $students = Student::whereDate('created_at', '>=', $start)
            ->whereDate('created_at', '<=', $end)
            ->select('created_at')
            ->get()
            ->groupBy(fn ($s) => Carbon::parse($s->created_at)->format('Y-m-d'))
            ->map(fn ($items) => $items->count());

        $deleted = Student::onlyTrashed()
            ->whereDate('deleted_at', '>=', $start)
            ->whereDate('deleted_at', '<=', $end)
            ->select('deleted_at')
            ->get()
            ->groupBy(fn ($s) => Carbon::parse($s->deleted_at)->format('Y-m-d'))
            ->map(fn ($items) => $items->count());

        $dates = [];
        $current = Carbon::parse($start);
        while ($current->lte(Carbon::parse($end))) {
            $dates[] = $current->format('Y-m-d');
            $current->addDay();
        }

        $studentsSeries = array_map(fn ($d) => $students->get($d, 0), $dates);
        $deletedSeries = array_map(fn ($d) => $deleted->get($d, 0), $dates);

        return [
            'labels' => $dates,
            'series' => ['students' => $studentsSeries, 'deleted_students' => $deletedSeries],
        ];
    }

    public function getGroupAttendance(?string $date, ?int $branchId, string $search = ''): array
    {
        $date = $date ?: now()->format('Y-m-d');

        $query = $this->groupAttendanceBaseQuery($date, $branchId, $search)
            ->whereHas('attendances', fn ($q) => $q->whereDate('date', $date));

        return $query->with(['teacher', 'course'])
            ->withCount('students')
            ->withCount(['attendances as comers' => fn ($q) => $q->whereDate('date', $date)->where('status', 'present')])
            ->withCount(['attendances as absent_count' => fn ($q) => $q->whereDate('date', $date)->where('status', 'absent')])
            ->withCount(['students as not_comers' => fn ($q) => $q->whereDoesntHave('attendances', fn ($aq) => $aq->whereDate('date', $date))])
            ->orderBy('name')
            ->get()
            ->map(fn ($g) => [
                'id' => $g->id,
                'name' => $g->name,
                'teacher_name' => $g->teacher?->name,
                'students_count' => $g->students_count,
                'comers' => $g->comers,
                'absent_count' => $g->absent_count,
                'not_comers' => $g->not_comers,
            ])
            ->toArray();
    }

    public function getGroupAttendanceNotExist(?string $date, ?int $branchId, string $search = ''): array
    {
        $date = $date ?: now()->format('Y-m-d');

        $query = $this->groupAttendanceBaseQuery($date, $branchId, $search)
            ->whereDoesntHave('attendances', fn ($q) => $q->whereDate('date', $date));

        return $query->with('teacher')
            ->withCount('students')
            ->orderBy('name')
            ->get()
            ->map(fn ($g) => [
                'id' => $g->id,
                'name' => $g->name,
                'teacher_name' => $g->teacher?->name,
                'students_count' => $g->students_count,
            ])
            ->toArray();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<\App\Models\Group>
     */
    private function groupAttendanceBaseQuery(string $date, ?int $branchId, string $search)
    {
        $dayOfWeek = strtolower(Carbon::parse($date)->format('l')); // monday, tuesday, etc.

        $query = Group::query()
            ->where('state', 'active')
            ->whereHas('groupTimes', fn ($q) => $q->where('name', $dayOfWeek));

        if ($branchId) {
            $query->whereHas('branches', fn ($q) => $q->where('branch_id', $branchId));
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhereHas('course', fn ($cq) => $cq->where('name', 'ilike', "%{$search}%"))
                    ->orWhereHas('teacher', fn ($tq) => $tq->where('name', 'ilike', "%{$search}%"));
            });
        }

        return $query;
    }

    public function getStudentsAgeChart(?int $branchId): array
    {
        $cacheKey = 'students_age_chart'.($branchId ? "_branch_{$branchId}" : '');

        $data = Cache::remember($cacheKey, now()->addDay(), function () use ($branchId) {
            $data = [
                ['age' => '0-11', 'count' => 0],
                ['age' => '12-15', 'count' => 0],
                ['age' => '16-17', 'count' => 0],
                ['age' => '17-...', 'count' => 0],
            ];

            $query = Student::whereNotNull('birthday')->select('birthday');
            if ($branchId) {
                $query->whereHas('branches', fn ($q) => $q->where('branch_id', $branchId));
            }
            $students = $query->get();

            foreach ($students as $student) {
                $age = Carbon::parse($student->birthday)->age;
                if ($age <= 11) {
                    $data[0]['count']++;
                } elseif ($age >= 12 && $age <= 15) {
                    $data[1]['count']++;
                } elseif ($age >= 16 && $age <= 17) {
                    $data[2]['count']++;
                } else {
                    $data[3]['count']++;
                }
            }

            return $data;
        });

        return [
            'labels' => array_column($data, 'age'),
            'series' => array_column($data, 'count'),
        ];
    }

    public function getStudentsGenderChart(?int $branchId): array
    {
        $cacheKey = 'report_gender_chart'.($branchId ? "_branch_{$branchId}" : '_all');

        $data = Cache::remember($cacheKey, now()->addHour(), function () use ($branchId) {
            $query = Student::query();
            if ($branchId) {
                $query->whereHas('branches', fn ($q) => $q->where('branch_id', $branchId));
            }
            $male = (clone $query)->where('gender', 'male')->count();
            $female = (clone $query)->where('gender', 'female')->count();

            return ['labels' => [__('attr.male'), __('attr.female')], 'series' => [$male, $female]];
        });

        return $data;
    }

    public function getTelegramConnectionStats(): array
    {
        $branchId = auth()->user()->current_branch_id;

        $totalStudents = Student::whereHas('branches', fn ($q) => $q->where('branch_id', $branchId))->count();
        $connectedStudents = Student::whereHas('branches', fn ($q) => $q->where('branch_id', $branchId))
            ->whereNotNull('telegram_chat_id')
            ->count();
        $notConnectedStudents = $totalStudents - $connectedStudents;
        $connectionPercentage = $totalStudents > 0 ? round(($connectedStudents / $totalStudents) * 100, 2) : 0;

        $groupStats = Group::whereHas('branches', fn ($q) => $q->where('branch_id', $branchId))
            ->withCount([
                'students as total_students',
                'students as connected_students' => fn ($q) => $q->whereNotNull('telegram_chat_id'),
            ])
            ->get()
            ->filter(fn ($g) => $g->total_students > 0)
            ->map(function ($g) {
                $g->not_connected_students = $g->total_students - $g->connected_students;
                $g->connection_percentage = $g->total_students > 0
                    ? round(($g->connected_students / $g->total_students) * 100, 2)
                    : 0;

                return $g;
            })
            ->values()
            ->toArray();

        $topGroups = collect($groupStats)->sortByDesc('connection_percentage')->take(5)->values()->toArray();
        $bottomGroups = collect($groupStats)->sortBy('connection_percentage')->take(5)->values()->toArray();

        return [
            'total_students' => $totalStudents,
            'connected_students' => $connectedStudents,
            'not_connected_students' => $notConnectedStudents,
            'connection_percentage' => $connectionPercentage,
            'chart_data' => [
                'labels' => [__('translation.telegram.connected'), __('translation.telegram.not_connected')],
                'series' => [$connectedStudents, $notConnectedStudents],
            ],
            'group_stats' => $groupStats,
            'top_groups' => $topGroups,
            'bottom_groups' => $bottomGroups,
        ];
    }

    /**
     * @return array{group: array{id: int, name: string}|null, students: list<array{id: int, full_name: string, telegram_chat_id: string|null}>}
     */
    public function getTelegramGroupStudentsForBranch(int $groupId): array
    {
        $branchId = auth()->user()->current_branch_id;

        $group = Group::query()
            ->whereHas('branches', fn ($q) => $q->where('branch_id', $branchId))
            ->whereKey($groupId)
            ->first(['id', 'name']);

        if ($group === null) {
            return ['group' => null, 'students' => []];
        }

        $students = $group->students()
            ->select(['students.id', 'students.name', 'students.surname', 'students.telegram_chat_id'])
            ->orderBy('students.surname')
            ->orderBy('students.name')
            ->get()
            ->map(fn (Student $student) => [
                'id' => $student->id,
                'full_name' => $student->getFullName(),
                'telegram_chat_id' => $student->telegram_chat_id,
            ])
            ->values()
            ->all();

        return [
            'group' => ['id' => $group->id, 'name' => $group->name],
            'students' => $students,
        ];
    }

    public function getHomeworkReport(
        ?string $startDate,
        ?string $endDate,
        string $search = '',
        ?int $groupId = null,
        ?int $teacherId = null,
        int $page = 1,
        int $perPage = 15
    ): array {
        [$start, $end] = $this->normalizeDateRange($startDate, $endDate);
        $branchId = auth()->user()->current_branch_id;

        $statsQuery = Attendance::query()
            ->whereBetween('date', [$start, $end])
            ->whereNotNull('homework')
            ->whereRelation('student', fn ($q) => $q->whereHas('branches', fn ($bq) => $bq->where('branch_id', $branchId)))
            ->when($groupId, fn ($q) => $q->where('group_id', $groupId))
            ->when($teacherId, fn ($q) => $q->whereHas('group', fn ($gq) => $gq->where('teacher_id', $teacherId)));

        $totalCompleted = (clone $statsQuery)->where('homework', true)->count();
        $totalNotCompleted = (clone $statsQuery)->where('homework', false)->count();
        $totalRecords = $statsQuery->count();
        $totalStudents = (clone $statsQuery)->distinct('student_id')->count('student_id');
        $completionRate = $totalRecords > 0 ? round(($totalCompleted / $totalRecords) * 100, 1) : 0;

        $studentsQuery = \App\Models\Student::query()
            ->select([
                'students.id',
                'students.name',
                'students.surname',
                'students.phone',
                'students.parent_phone',
                'groups.name as group_name',
            ])
            ->selectRaw('COUNT(CASE WHEN attendances.homework = true THEN 1 END) as completed_count')
            ->selectRaw('COUNT(CASE WHEN attendances.homework = false THEN 1 END) as not_completed_count')
            ->selectRaw('COUNT(attendances.id) as total_homework')
            ->join('attendances', 'students.id', '=', 'attendances.student_id')
            ->join('groups', 'attendances.group_id', '=', 'groups.id')
            ->whereBetween('attendances.date', [$start, $end])
            ->whereNotNull('attendances.homework')
            ->whereHas('branches', fn ($q) => $q->where('branch_id', $branchId))
            ->when($search, fn ($q) => $q->where(function ($sq) use ($search) {
                $sq->where('students.name', 'ilike', "%{$search}%")
                    ->orWhere('students.surname', 'ilike', "%{$search}%")
                    ->orWhere('students.phone', 'ilike', "%{$search}%")
                    ->orWhere('groups.name', 'ilike', "%{$search}%");
            }))
            ->when($groupId, fn ($q) => $q->where('groups.id', $groupId))
            ->when($teacherId, fn ($q) => $q->where('groups.teacher_id', $teacherId))
            ->groupBy('students.id', 'students.name', 'students.surname', 'students.phone', 'students.parent_phone', 'groups.name')
            ->orderByRaw('COUNT(CASE WHEN attendances.homework = false THEN 1 END) DESC')
            ->orderByRaw('COUNT(attendances.id) DESC');

        $students = $studentsQuery->paginate($perPage, ['*'], 'page', $page);

        return [
            'stats' => [
                'total_completed' => $totalCompleted,
                'total_not_completed' => $totalNotCompleted,
                'total_records' => $totalRecords,
                'total_students' => $totalStudents,
                'completion_rate' => $completionRate,
            ],
            'students' => $students,
            'groups' => Group::query()
                ->active()
                ->when($teacherId, fn ($q) => $q->where('teacher_id', $teacherId))
                ->with('teacher:id,name')
                ->orderBy('name')
                ->get(['id', 'name', 'teacher_id']),
            'teachers' => User::whereHas('groups', fn ($q) => $q->where('state', 'active'))->orderBy('name')->get(['id', 'name']),
        ];
    }

    public function getUserCallsReport(?string $startDate, ?string $endDate, string $search = ''): array
    {
        [$start, $end] = $this->normalizeDateRange($startDate, $endDate);
        $branchId = auth()->user()->current_branch_id;

        // Student calls
        $studentCalls = \App\Models\Call::query()
            ->join('users', 'student_calls.user_id', '=', 'users.id')
            ->select(
                'users.id',
                'users.name',
                \Illuminate\Support\Facades\DB::raw('count(student_calls.id) as total_calls'),
                \Illuminate\Support\Facades\DB::raw("sum(case when student_calls.status = 'answered' then 1 else 0 end) as answered_calls"),
                \Illuminate\Support\Facades\DB::raw("sum(case when student_calls.status = 'not_answered' then 1 else 0 end) as not_answered_calls"),
                \Illuminate\Support\Facades\DB::raw("sum(case when student_calls.status = 'busy' then 1 else 0 end) as busy_calls"),
                \Illuminate\Support\Facades\DB::raw("sum(case when student_calls.status = 'no_answer' then 1 else 0 end) as no_answer_calls"),
                \Illuminate\Support\Facades\DB::raw('sum(student_calls.duration) as total_duration'),
                \Illuminate\Support\Facades\DB::raw('count(student_calls.id) as student_calls_count'),
                \Illuminate\Support\Facades\DB::raw('0 as lead_calls_count'),
                // Breakdown for students
                \Illuminate\Support\Facades\DB::raw("sum(case when student_calls.status = 'answered' then 1 else 0 end) as student_answered_calls"),
                \Illuminate\Support\Facades\DB::raw("sum(case when student_calls.status = 'not_answered' then 1 else 0 end) as student_not_answered_calls"),
                \Illuminate\Support\Facades\DB::raw("sum(case when student_calls.status = 'busy' then 1 else 0 end) as student_busy_calls"),
                \Illuminate\Support\Facades\DB::raw("sum(case when student_calls.status = 'no_answer' then 1 else 0 end) as student_no_answer_calls"),
                // Placeholder for lead columns
                \Illuminate\Support\Facades\DB::raw('0 as lead_answered_calls'),
                \Illuminate\Support\Facades\DB::raw('0 as lead_not_answered_calls'),
                \Illuminate\Support\Facades\DB::raw('0 as lead_busy_calls'),
                \Illuminate\Support\Facades\DB::raw('0 as lead_no_answer_calls')
            )
            ->whereDate('student_calls.called_at', '>=', $start)
            ->whereDate('student_calls.called_at', '<=', $end)
            ->whereHas('student.branches', fn ($q) => $q->where('branch_id', $branchId))
            ->groupBy('users.id', 'users.name');

        if ($search) {
            $studentCalls->where('users.name', 'ilike', '%'.$search.'%');
        }

        // Lead calls
        $leadCalls = \App\Models\LeadCall::query()
            ->join('users', 'lead_calls.causer_id', '=', 'users.id')
            ->select(
                'users.id as id',
                'users.name as name',
                \Illuminate\Support\Facades\DB::raw('count(lead_calls.id) as total_calls'),
                \Illuminate\Support\Facades\DB::raw("sum(case when lead_calls.status = 'answered' then 1 else 0 end) as answered_calls"),
                \Illuminate\Support\Facades\DB::raw("sum(case when lead_calls.status = 'not_answered' then 1 else 0 end) as not_answered_calls"),
                \Illuminate\Support\Facades\DB::raw("sum(case when lead_calls.status = 'busy' then 1 else 0 end) as busy_calls"),
                \Illuminate\Support\Facades\DB::raw("sum(case when lead_calls.status = 'no_answer' then 1 else 0 end) as no_answer_calls"),
                \Illuminate\Support\Facades\DB::raw('0 as total_duration'),
                \Illuminate\Support\Facades\DB::raw('0 as student_calls_count'),
                \Illuminate\Support\Facades\DB::raw('count(lead_calls.id) as lead_calls_count'),
                // Breakdown for lead
                \Illuminate\Support\Facades\DB::raw('0 as student_answered_calls'),
                \Illuminate\Support\Facades\DB::raw('0 as student_not_answered_calls'),
                \Illuminate\Support\Facades\DB::raw('0 as student_busy_calls'),
                \Illuminate\Support\Facades\DB::raw('0 as student_no_answer_calls'),
                \Illuminate\Support\Facades\DB::raw("sum(case when lead_calls.status = 'answered' then 1 else 0 end) as lead_answered_calls"),
                \Illuminate\Support\Facades\DB::raw("sum(case when lead_calls.status = 'not_answered' then 1 else 0 end) as lead_not_answered_calls"),
                \Illuminate\Support\Facades\DB::raw("sum(case when lead_calls.status = 'busy' then 1 else 0 end) as lead_busy_calls"),
                \Illuminate\Support\Facades\DB::raw("sum(case when lead_calls.status = 'no_answer' then 1 else 0 end) as lead_no_answer_calls")
            )
            ->whereDate('lead_calls.created_at', '>=', $start)
            ->whereDate('lead_calls.created_at', '<=', $end)
            ->groupBy('users.id', 'users.name');

        if ($search) {
            $leadCalls->where('users.name', 'ilike', '%'.$search.'%');
        }

        // Union both and aggregate again
        $unioned = \Illuminate\Support\Facades\DB::table(\Illuminate\Support\Facades\DB::raw("({$studentCalls->toSql()}) as student_calls_sub"))
            ->mergeBindings($studentCalls->toBase())
            ->unionAll($leadCalls->toBase());

        $finalQuery = \Illuminate\Support\Facades\DB::table(\Illuminate\Support\Facades\DB::raw("({$unioned->toSql()}) as combined"))
            ->mergeBindings($unioned)
            ->select(
                'id',
                'name',
                \Illuminate\Support\Facades\DB::raw('sum(total_calls) as total_calls'),
                \Illuminate\Support\Facades\DB::raw('sum(answered_calls) as answered_calls'),
                \Illuminate\Support\Facades\DB::raw('sum(not_answered_calls) as not_answered_calls'),
                \Illuminate\Support\Facades\DB::raw('sum(busy_calls) as busy_calls'),
                \Illuminate\Support\Facades\DB::raw('sum(no_answer_calls) as no_answer_calls'),
                \Illuminate\Support\Facades\DB::raw('sum(total_duration) as total_duration'),
                \Illuminate\Support\Facades\DB::raw('sum(student_calls_count) as student_calls_count'),
                \Illuminate\Support\Facades\DB::raw('sum(lead_calls_count) as lead_calls_count'),
                \Illuminate\Support\Facades\DB::raw('sum(student_answered_calls) as student_answered_calls'),
                \Illuminate\Support\Facades\DB::raw('sum(student_not_answered_calls) as student_not_answered_calls'),
                \Illuminate\Support\Facades\DB::raw('sum(student_busy_calls) as student_busy_calls'),
                \Illuminate\Support\Facades\DB::raw('sum(student_no_answer_calls) as student_no_answer_calls'),
                \Illuminate\Support\Facades\DB::raw('sum(lead_answered_calls) as lead_answered_calls'),
                \Illuminate\Support\Facades\DB::raw('sum(lead_not_answered_calls) as lead_not_answered_calls'),
                \Illuminate\Support\Facades\DB::raw('sum(lead_busy_calls) as lead_busy_calls'),
                \Illuminate\Support\Facades\DB::raw('sum(lead_no_answer_calls) as lead_no_answer_calls')
            )
            ->groupBy('id', 'name')
            ->orderByDesc('total_calls');

        return $finalQuery->get()->toArray();
    }

    private function getAttendancePercentage(string $startDate, string $endDate, int $branchId): int
    {
        $present = Attendance::whereRelation('student', fn ($q) => $q->whereHas('branches', fn ($bq) => $bq->where('branch_id', $branchId)))
            ->whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate)
            ->where('status', 'present')
            ->count();

        $absent = Attendance::whereRelation('student', fn ($q) => $q->whereHas('branches', fn ($bq) => $bq->where('branch_id', $branchId)))
            ->whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate)
            ->where('status', 'absent')
            ->count();

        $total = $present + $absent;

        return $total > 0 ? (int) round(($present / $total) * 100) : 0;
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function normalizeDateRange(?string $start, ?string $end): array
    {
        if (! $start || ! $end) {
            $start = now()->subDays(30)->format('Y-m-d');
            $end = now()->format('Y-m-d');
        }

        return [$start, $end];
    }
}
