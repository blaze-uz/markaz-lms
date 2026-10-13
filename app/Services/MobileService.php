<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Expense;
use App\Models\Group;
use App\Models\GroupTime;
use App\Models\Payment;
use App\Models\Room;
use App\Models\Student;
use Carbon\Carbon;

class MobileService
{
    public function login(array $data)
    {
        if (! auth()->attempt($data)) {
            return response()->json([
                'message' => 'Invalid credentials!',
            ], 401);
        }

        $user = auth()->user();
        $token = $user->createToken('mobile-token')->plainTextToken;
        $branch = $user->branches()->first();

        return [$user, $token, $branch];
    }

    public function getBranches()
    {
        return auth()->user()->branches()->get();
    }

    public function getGroupsCount()
    {
        return auth()->user()->groups()->count();
    }

    public function getStudentsCount()
    {
        $user = auth()->user();

        return Student::whereHas('groups', function ($query) use ($user) {
            $query->whereIn('group_id', $user->groups()->pluck('id'));
        })->count();
    }

    public function getStudentsChart()
    {
        $series = $this->getStudentsChartSeries();
        $labels = $this->getStudentsChartLabels();

        return [
            'series' => $series,
            'labels' => $labels,
        ];
    }

    private function getStudentsChartSeries()
    {
        // Initialize the series array
        $series = [];

        // Define the months from start of the year to end of the year
        $months = [];
        for ($i = 1; $i <= 12; $i++) {
            $months[] = $i;
        }

        // Cache key prefix
        $cacheKeyPrefix = 'teacher_user_counts_';

        // Retrieve user counts for each month
        foreach ($months as $month) {
            $count = $this->getUserCountForMonth($month);
            $series[] = $count;
        }

        return $series;
    }

    private function getUserCountForMonth(mixed $month)
    {
        $groups = auth()->user()->groups()->pluck('id');

        return Student::whereHas('groups', function ($query) use ($groups) {
            $query->whereIn('group_id', $groups);
        })
            ->whereYear('students.created_at', date('Y'))
            ->whereMonth('students.created_at', $month)
            ->count();
    }

    private function getStudentsChartLabels(): \Illuminate\Support\Collection
    {
        return collect(range(1, 12))->map(function ($month) {
            return __('translation.months.'.strtolower(date('F', mktime(0, 0, 0, $month, 1))));
        });
    }

    public function getStudentsByGender(): array
    {
        $labels = [__('attr.male'), __('attr.female')];

        $groups = auth()->user()->groups()->pluck('id');

        $men = Student::whereHas('groups', function ($query) use ($groups) {
            $query->whereIn('group_id', $groups);
        })->where('gender', 'male')->count();
        $women = Student::whereHas('groups', function ($query) use ($groups) {
            $query->whereIn('group_id', $groups);
        })->where('gender', 'female')->count();

        $series = [$men, $women];

        return [
            'labels' => $labels,
            'series' => $series,
        ];
    }

    public function getStudentsFinancialStatusChart(): array
    {
        $groups = auth()->user()->groups()->pluck('id');

        $unpaid = Student::whereHas('groups', function ($query) use ($groups) {
            $query->whereIn('group_id', $groups);
        })->whereRelation('groupPayments', 'left_amount', '!=', 0)->count();

        $paid = Student::whereHas('groups', function ($query) use ($groups) {
            $query->whereIn('group_id', $groups);
        })->whereRelation('groupPayments', 'left_amount', '=', 0)->count();

        $labels = [__('attr.paid'), __('attr.unpaid')];
        $series = [$paid, $unpaid];

        return [
            'labels' => $labels,
            'series' => $series,
        ];
    }

    public function me(): array
    {
        $user = auth()->user();
        $role = $user->roles()->first();
        $permissions = $role->permissions()->pluck('name');
        $branches = $user->branches()->get();
        $currentBranch = $user->currentBranch;

        return [
            'user' => $user,
            'currentBranch' => $currentBranch,
            'branches' => $branches,
            'role' => $role,
            'permissions' => $permissions,
        ];
    }

    public function getStudentsLineChart(): array
    {
        $labels = $this->getStudentsChartLabels();
        $series = [];

        $currentYear = date('Y');

        $months = [];
        for ($i = 1; $i <= 12; $i++) {
            $month = str_pad($i, 2, '0', STR_PAD_LEFT); // Zero-padding month
            $months[] = $currentYear.'-'.$month;
        }

        foreach ($months as $month) {
            $count = $this->getStudentsCountForMonth($month);

            $series[] = $count;
        }

        return [
            'series' => $series,
            'labels' => $labels,
        ];
    }

    private function getStudentsCountForMonth(mixed $month)
    {
        // Get the current year and month
        $currentYear = date('Y');
        $currentMonth = date('m');

        // Extract year and month from the given month string
        $givenYear = substr($month, 0, 4);
        $givenMonth = substr($month, 5);

        // If the given year is greater than the current year or
        // if the given year is the same but the given month is greater than the current month,
        // return null
        if ($givenYear > $currentYear || ($givenYear == $currentYear && $givenMonth > $currentMonth)) {
            return 0;
        }

        // Calculate the last day of the month
        $lastDayOfMonth = date('Y-m-t', strtotime($month));

        $groups = auth()->user()->groups()->pluck('id');

        // Retrieve the student count until the last day of the month
        return Student::whereHas('groups', function ($query) use ($groups) {
            $query->whereIn('group_id', $groups);
        })->where('created_at', '<=', $lastDayOfMonth.' 23:59:59')
            ->count();
    }

    public function getCalendarData($date): array
    {
        // check date is valid
        if (! strtotime($date)) {
            $date = now()->format('Y-m-d');
        }
        $startDate = $date;
        $endDate = $date;

        $colors = [
            '#6A9C89',
            '#1AACAC',
            '#8294C4',
            '#5C5470',
            '#1AACAC',
            '#DFA67B',
            '#654E92',
            '#F44336',
            '#E91E63',
            '#9C27B0',
            '#673AB7',
            '#3F51B5',
            '#2196F3',
            '#03A9F4',
            '#00BCD4',
            '#009688',
            '#4CAF50',
        ];

        $events = [];

        // Loop through the days in the specified date range
        $currentDate = $startDate;
        while ($currentDate <= $endDate) {
            // Determine the day of the week (e.g., Monday, Tuesday) based on the date
            $dayOfWeek = date('l', strtotime($currentDate));

            // Retrieve the data for the specific day of the week
            $groups = auth()->user()->groups()->get();
            if (request()->group_id) {
                $groups = auth()->user()->groups()->where('id', request()->group_id)->get();
            }
            $data = GroupTime::with('group.teacher')->whereIn('group_id', $groups->pluck('id')->toArray())
                ->where('name', strtolower($dayOfWeek)) // 'name' contains the day of the week
                ->get();

            if ($data) {
                foreach ($data as $item) {
                    $events[] = [
                        'id' => $item->id,
                        'room' => $item->room?->name ?? '-',
                        'start' => "$item->start",
                        'end' => "$item->end",
                        'group' => $item->group?->name ?? '-',
                        'description' => $item->start.' - '.$item->end,
                        'color' => $colors[rand(0, 15)],
                        'textColor' => '#ffffff',
                    ];
                }
            }

            $currentDate = date('Y-m-d', strtotime($currentDate.' +1 day'));
        }

        return $events;
    }

    public function getRooms()
    {
        return Room::select('id', 'name')->get();
    }

    public function getRoomTimeline(mixed $room)
    {
        if (! $room || ! is_numeric($room)) {
            return [
                'monday' => [],
                'tuesday' => [],
                'wednesday' => [],
                'thursday' => [],
                'friday' => [],
                'saturday' => [],
                'sunday' => [],
            ];
        }

        $groupTimes = GroupTime::with('group.teacher')
            ->where('room_id', $room)
            ->orderBy('start')
            ->get();

        // Define an array to hold grouped data with all weekdays
        $weekdays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $groupedTimes = array_fill_keys($weekdays, []);

        // Group groupTimes by day of the week
        foreach ($groupTimes as $time) {
            if ($time->group == null) {
                continue;
            }
            // Get the day of the week for the current group time

            // Append the current group time data to the day of the week
            $groupedTimes[$time->name][] = [
                'group' => $time->group->name,
                'group_id' => $time->group->id,
                'teacher' => $time->group->teacher?->name ?? 'No teacher assigned',
                'start' => $time->start,
                'end' => $time->end,
            ];
        }

        $groupedTimes['room_id'] = $room;

        return $groupedTimes;
    }

    public function getGroups()
    {
        $user = auth()->user();

        return Group::where('teacher_id', $user->id)->get()->map(function ($group) {
            return [
                'id' => $group->id,
                'name' => $group->name,
                'course_name' => $group->course?->name ?? '-',
                'state' => __('translation.groups.'.$group->state),
                'students_count' => $group->students()->count(),
                'price' => $group->price,
                'created_at' => $group->created_at,
            ];
        });
    }

    public function getGroupsCardData()
    {
        $user = auth()->user();
        $allGroupsCount = Group::where('teacher_id', $user->id)->count();
        $demoGroupsCount = Group::where('teacher_id', $user->id)->demo()->count();
        $activeGroupsCount = Group::where('teacher_id', $user->id)->active()->count();
        $completedGroupsCount = Group::where('teacher_id', $user->id)->completed()->count();

        return [
            'allGroupsCount' => $allGroupsCount,
            'demoGroupsCount' => $demoGroupsCount,
            'activeGroupsCount' => $activeGroupsCount,
            'completedGroupsCount' => $completedGroupsCount,
        ];
    }

    public function getGroup($group)
    {
        $user = auth()->user();
        $group = Group::where('teacher_id', $user->id)->findOrFail($group);

        return [
            'id' => $group->id,
            'name' => $group->name,
            'course_id' => $group->course_id,
            'teacher_id' => $group->teacher_id,
            'course_name' => $group->course?->name ?? '-',
            'students_count' => $group->students()->count(),
            'price' => $group->price,
            'created_at' => $group->created_at,
            'has_attendance' => $group->has_attendance,
            'payment_type' => __('translation.groups.'.$group->payment_type),
            'lesson_count' => $group->lesson_count,
            'start_date' => $group->start_date,
            'state' => __('translation.groups.'.$group->state),
            'account_id' => $group->account_id,
            'completed_at' => $group->completed_at,
            'teacher_percentage' => $group->teacher_percentage,
        ];
    }

    public function getGroupCardData($group): array
    {
        $group = $this->getGroup($group);
        $students = Student::whereRelation('groups', 'group_id', $group['id']);
        $groupTimesQuery = GroupTime::where('group_id', $group['id'])->get();
        $studentsCount = $students->count();
        $debtStudentsCount = $students->whereRelation('groupPayments', 'left_amount', '!=', 0)->count();
        $groupTimes = $groupTimesQuery->where('name', strtolower(date('l')))->first();

        return [
            'studentsCount' => $studentsCount,
            'debtStudentsCount' => $debtStudentsCount,
            'groupTimes' => $groupTimes?->start.' - '.$groupTimes?->end,
        ];
    }

    public function getGroupStudents($group)
    {
        $group = $this->getGroup($group);
        $students = Student::with('account', 'groups')->whereRelation('groups', 'group_id', $group['id'])->get();

        return $students->map(function ($student) use ($group) {
            return [
                'id' => $student->id,
                'name' => $student->name,
                'surname' => $student->surname,
                'phone' => $student->getFormattedPhone(),
                'balance' => $student->account->getBalance(),
                'debt' => $student->groupPayments->sum('left_amount'),
                'attached_at' => dateFormat($student->groups->where('id', $group['id'])->first()->pivot->attached_at),
            ];
        });
    }

    public function getGroupAttendance($group, $range)
    {
        $startDate = now()->subDays($range);
        $endDate = now();
        $group = Group::where('teacher_id', auth()->user()->id)->findOrFail($group);
        $attendanceData = $group->attendances()
            ->whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate)
            ->orderBy('date')
            ->get();

        $uniqueDays = array_values($attendanceData->pluck('date')->unique()->toArray());

        $students = Student::withTrashed()
            ->whereIn('id', $attendanceData->pluck('student_id'))
            ->get(['id', 'name', 'surname', 'account_id'])
            ->map(function ($student) use ($attendanceData) {
                $student->attendance = $attendanceData->where('student_id', $student->id)->unique('date')
                    ->values()
                    ->map(function ($attendance) {
                        return [
                            'date' => $attendance->date,
                            'status' => $attendance->status,
                            'comment' => $attendance->comment,
                            'score' => $attendance->score,
                        ];
                    });

                return $student;
            });

        return [
            // 'attendanceData' => $attendanceData,
            'uniqueDays' => $uniqueDays,
            'students' => $students,
        ];
    }

    public function logout(): void
    {
        auth()->user()->tokens()->delete();
    }

    public function storeAttendance($group, array $data)
    {
        $group = Group::where('teacher_id', auth()->user()->id)->findOrFail($group);

        //validation for attendance exists
        if ($group->attendances()->where('date', $data['date'])->exists()) {
            return [
                'message' => 'Attendance already exists for this date!',
                'status' => 'error',
            ];
        }

        foreach ($data['students'] as $student) {
            $group->attendances()->create([
                'date' => $data['date'],
                'student_id' => $student['id'],
                'status' => $student['status'],
                'comment' => $student['comment'] ?? null,
                'score' => $student['score'] ?? null,
            ]);
        }

        return [
            'message' => 'Attendance stored successfully',
            'status' => 'success',
        ];
    }

    public function updateAttendance($group, array $data): void
    {
        //remove old attendance
        $group = Group::where('teacher_id', auth()->user()->id)->findOrFail($group);
        $group->attendances()->where('date', $data['date'])->delete();

        //store new attendance
        $this->storeAttendance($group->id, $data);
    }

    public function getAttendance($group, $date)
    {
        $group = Group::where('teacher_id', auth()->user()->id)->findOrFail($group);

        return Attendance::where('group_id', $group->id)
            ->whereDate('date', $date)
            ->distinct('student_id')
            ->get()
            ->map(function ($attendance) {
                return [
                    'student_id' => $attendance->student_id,
                    'student_name' => $attendance->student->getFullName() ?? '-',
                    'status' => $attendance->status,
                    'comment' => $attendance->comment,
                    'score' => $attendance->score,
                ];
            });
    }

    public function getDashboardData()
    {
        $params = $this->getRequestParams(request()->all());
        $groupsCount = $this->getGroupsCount();
        $studentsCount = $this->getStudentsCount();
        $studentsChart = $this->getStudentsChart();
        $studentsByGender = $this->getStudentsByGender();
        $studentsFinancialStatusChart = $this->getStudentsFinancialStatusChart();
        $studentsLineChart = $this->getStudentsLineChart();
        $calendar = $this->getCalendarData(request()->date ?? now()->format('Y-m-d'));
        $rooms = $this->getRooms();
        $roomTimeline = $this->getRoomTimeline(request()->room_id ?? $rooms->first()?->id ?? null);
        $income_and_expense = $this->getIncomeAndExpense($params);

        return [
            'groupsCount' => $groupsCount,
            'studentsCount' => $studentsCount,
            'studentsChart' => $studentsChart,
            'studentsByGender' => $studentsByGender,
            'studentsFinancialStatusChart' => $studentsFinancialStatusChart,
            'studentsLineChart' => $studentsLineChart,
            'calendar' => $calendar,
            'rooms' => $rooms,
            'roomTimeline' => $roomTimeline,
            'incomeAndExpenseChart' => $income_and_expense,
        ];
    }

    public function getGroupData($group)
    {
        $groupData = $this->getGroup($group);
        $card = $this->getGroupCardData($group);
        $students = $this->getGroupStudents($group);
        $attendance = $this->getGroupAttendance($group, request()->range ?? '7');

        return [
            'group' => $groupData,
            'card' => $card,
            'students' => $students,
            'attendance' => $attendance,
        ];
    }

    public function changeCurrentBranch(array $data)
    {
        $user = auth()->user();
        $branch = $user->branches()->findOrFail($data['branch_id']);
        $user->update(['current_branch_id' => $branch->id]);
    }

    public function getIncomeAndExpense($data)
    {
        if (! auth()->user()->can('report-index')) {
            return [
                'income' => null,
                'expense' => null,
            ];
        }

        $startDate = Carbon::parse($data['from']);
        $endDate = Carbon::parse($data['to']);
        $chartData = [
            'income' => [],
            'expense' => [],
        ];

        // Aggregate income by date
        $payments = Payment::with(['student' => function ($query) {
            $query->withTrashed();
        }])
            ->whereBetween('date', [$startDate, $endDate])
            ->doesntHave('refunds')
            ->groupBy('date')
            ->selectRaw('date, SUM(amount) as total_amount')
            ->get();

        // Aggregate expenses by date
        $expenses = Expense::whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('created_at')
            ->selectRaw('DATE(created_at) as date, SUM(price) as total_price')
            ->get();

        // Initialize arrays to hold daily totals
        $dailyIncome = [];
        $dailyExpense = [];

        // Populate daily income
        foreach ($payments as $payment) {
            $date = Carbon::parse($payment->date)->toDateString();
            $dailyIncome[$date] = $payment->total_amount;
        }

        // Populate daily expenses
        foreach ($expenses as $expense) {
            $date = Carbon::parse($expense->date)->toDateString();
            $dailyExpense[$date] = $expense->total_price;
        }

        // Fill in chart data for each day in the date range
        $currentDate = $startDate->copy();
        while ($currentDate->lte($endDate)) {
            $formattedDate = $currentDate->toDateString();
            $chartData['income'][$formattedDate] = $dailyIncome[$formattedDate] ?? 0;
            $chartData['expense'][$formattedDate] = $dailyExpense[$formattedDate] ?? 0;
            $currentDate->addDay(); // Move to the next day
        }

        return $chartData;
    }

    public function getRequestParams($data)
    {
        if (! isset($data['from']) && ! isset($data['to'])) {
            $data['from'] = now()->subDays(30);
            $data['to'] = now();
        } else {
            $data['from'] = Carbon::parse(str_replace('.', '-', $data['from']))->format('Y-m-d');
            $data['to'] = Carbon::parse(str_replace('.', '-', $data['to']))->format('Y-m-d');
        }

        return $data;
    }
}
