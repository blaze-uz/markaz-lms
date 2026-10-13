<?php

namespace App\Http\Controllers\Admin\Reports;

use App\Exports\AttendanceExport;
use App\Exports\StudentsChartExport;
use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\User;
use App\Services\EducationalReportService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class EducationalReportController extends Controller
{
    public function __construct(
        private EducationalReportService $service
    ) {
        $this->middleware('permission:report-educational');
    }

    public function index(Request $request)
    {
        Inertia::setRootView('layouts.admin');

        $fromInput = $this->normalizeDateInput($request->input('from'));
        $toInput = $this->normalizeDateInput($request->input('to'));

        $from = $fromInput ? date('Y-m-d', strtotime($fromInput)) : null;
        $to = $toInput ? date('Y-m-d', strtotime($toInput)) : null;

        return Inertia::render('Reports/Educational/Index', [
            'translations' => $this->getTranslations(),
            'filters' => [
                'from' => $from,
                'to' => $to,
            ],
        ]);
    }

    public function attendanceDetail(Request $request)
    {
        Inertia::setRootView('layouts.admin');

        $from = $request->input('from') ? date('Y-m-d', strtotime($request->input('from'))) : null;
        $to = $request->input('to') ? date('Y-m-d', strtotime($request->input('to'))) : null;
        if (! $from || ! $to) {
            $from = now()->subDays(30)->format('Y-m-d');
            $to = now()->format('Y-m-d');
        }

        $search = (string) ($request->input('search') ?? '');
        $requestedGroupId = $request->input('group_id') ? (int) $request->input('group_id') : null;
        $requestedTeacherId = $request->input('teacher_id') ? (int) $request->input('teacher_id') : null;
        $page = max(1, (int) $request->input('page', 1));

        $teachers = User::query()
            ->whereHas('groups', fn ($q) => $q->where('state', 'active'))
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (User $u) => ['id' => $u->id, 'name' => $u->name])
            ->values()
            ->all();

        $teacherIds = array_column($teachers, 'id');
        $teacherId = ($requestedTeacherId && in_array($requestedTeacherId, $teacherIds, true)) ? $requestedTeacherId : null;

        $groups = Group::active()
            ->orderBy('name')
            ->get(['id', 'name', 'teacher_id'])
            ->map(fn (Group $g) => [
                'id' => $g->id,
                'name' => $g->name,
                'teacher_id' => $g->teacher_id ? (int) $g->teacher_id : null,
            ])
            ->values()
            ->all();

        $allowedGroupIds = array_column(array_filter(
            $groups,
            fn (array $g) => $teacherId === null || (int) ($g['teacher_id'] ?? 0) === $teacherId
        ), 'id');

        $groupId = ($requestedGroupId && in_array($requestedGroupId, $allowedGroupIds, true)) ? $requestedGroupId : null;

        $data = $this->service->getAttendanceDetail($from, $to, $search, $groupId, $teacherId, 25, $page);

        return Inertia::render('Reports/Educational/AttendanceDetail', [
            'translations' => $this->getTranslations(),
            'filters' => [
                'from' => $from,
                'to' => $to,
                'search' => $search,
                'group_id' => $groupId,
                'teacher_id' => $teacherId,
            ],
            'teachers' => $teachers,
            'groups' => $groups,
            'summary' => $data['summary'],
            'records' => $data['records'],
        ]);
    }

    public function cards(Request $request)
    {
        $from = $request->input('from');
        $to = $request->input('to');

        return response()->json($this->service->getCardsData($from, $to));
    }

    public function charts(Request $request)
    {
        $from = $request->input('from');
        $to = $request->input('to');
        $branchId = auth()->user()->current_branch_id;

        return response()->json([
            'attendanceChart' => $this->service->getAttendanceChartData($from, $to),
            'homeworkChart' => $this->service->getHomeworkChartData($from, $to),
            'studentsChart' => $this->service->getStudentsChartData($from, $to),
            'studentsAgeChart' => $this->service->getStudentsAgeChart($branchId),
            'studentsGenderChart' => $this->service->getStudentsGenderChart($branchId),
        ]);
    }

    public function telegramStats()
    {
        return response()->json($this->service->getTelegramConnectionStats());
    }

    public function telegramGroupStudents(Request $request)
    {
        $groupId = max(0, (int) $request->query('group_id', 0));

        if ($groupId < 1) {
            return response()->json(['group' => null, 'students' => []], 422);
        }

        return response()->json($this->service->getTelegramGroupStudentsForBranch($groupId));
    }

    public function userCallsReport(Request $request)
    {
        $from = $request->input('from');
        $to = $request->input('to');
        $search = $request->input('search') ?? '';

        $data = $this->service->getUserCallsReport($from, $to, $search);

        return response()->json($data);
    }

    public function groupAttendance(Request $request)
    {
        $date = $request->input('date', now()->format('Y-m-d'));
        $branchId = auth()->user()->current_branch_id;
        $search = $request->input('search') ?? '';

        $groups = $this->service->getGroupAttendance($date, $branchId, $search);
        $groupsNotExist = $this->service->getGroupAttendanceNotExist($date, $branchId, $search);

        return response()->json([
            'groups' => $groups,
            'groupsNotExist' => $groupsNotExist,
        ]);
    }

    public function homeworkReport(Request $request)
    {
        $from = $request->input('from');
        $to = $request->input('to');
        $homeworkFrom = $request->filled('homework_from') ? $request->input('homework_from') : $from;
        $homeworkTo = $request->filled('homework_to') ? $request->input('homework_to') : $to;
        $search = $request->input('search') ?? '';
        $groupId = $request->input('group_id') ? (int) $request->input('group_id') : null;
        $teacherId = $request->input('teacher_id') ? (int) $request->input('teacher_id') : null;
        $page = (int) $request->input('page', 1);

        $data = $this->service->getHomeworkReport($homeworkFrom, $homeworkTo, $search, $groupId, $teacherId, $page, 15);

        return response()->json($data);
    }

    public function exportAttendance(Request $request)
    {
        $from = $this->normalizeDateInput($request->input('from')) ?: now()->subDays(30)->format('Y-m-d');
        $to = $this->normalizeDateInput($request->input('to')) ?: now()->format('Y-m-d');
        $fileName = 'attendance_report_'.$from.'_to_'.$to.'.xlsx';

        return Excel::download(
            new AttendanceExport($from, $to, auth()->user()->current_branch_id),
            $fileName
        );
    }

    public function exportStudents(Request $request)
    {
        $from = $this->normalizeDateInput($request->input('from')) ?: now()->subDays(30)->format('Y-m-d');
        $to = $this->normalizeDateInput($request->input('to')) ?: now()->format('Y-m-d');
        $fileName = 'students_report_'.$from.'_to_'.$to.'.xlsx';

        return Excel::download(
            new StudentsChartExport($from, $to, auth()->user()->current_branch_id),
            $fileName
        );
    }

    /**
     * @return array<string, string>
     */
    private function getTranslations(): array
    {
        return [
            'reports_education' => __('translation.reports.education'),
            'reports_attendance' => __('translation.reports.attendance'),
            'reports_attendance_chart' => __('translation.reports.attendance_chart'),
            'reports_attendance_detail' => __('translation.reports.attendance_detail'),
            'reports_back_to_educational' => __('translation.reports.back_to_educational'),
            'reports_detailed' => __('translation.reports.detailed'),
            'reports_students_chart' => __('translation.reports.students_chart'),
            'reports_students_chart_by_age' => __('translation.reports.students_chart_by_age'),
            'reports_today_group_attendance_not_exist' => __('translation.reports.today_group_attendance_not_exist'),
            'reports_chart' => __('translation.reports.chart'),
            'groups_students_count' => __('translation.groups.students_count'),
            'groups_teachers_count' => __('translation.users.teachers_count'),
            'groups_homework' => __('translation.groups.homework'),
            'groups_name' => __('translation.groups.name'),
            'groups_teacher' => __('translation.groups.teacher'),
            'groups_group' => __('translation.groups.group'),
            'students_students' => __('translation.students.students'),
            'students_deleted_students' => __('translation.students.deleted_students'),
            'students_deleted_students_count' => __('translation.students.deleted_students_count'),
            'students_full_name' => __('translation.students.full_name'),
            'students_phone' => __('translation.students.phone'),
            'students_chart_by_gender' => __('translation.students_chart_by_gender'),
            'attr_from' => __('attr.from'),
            'attr_to' => __('attr.to'),
            'attr_today' => __('attr.today'),
            'attr_yesterday' => __('attr.yesterday'),
            'attr_last7days' => __('attr.last7days'),
            'attr_last30days' => __('attr.last30days'),
            'attr_thisMonth' => __('attr.thisMonth'),
            'attr_lastMonth' => __('attr.lastMonth'),
            'attr_thisYear' => __('attr.thisYear'),
            'attr_lastYear' => __('attr.lastYear'),
            'attr_date' => __('attr.date'),
            'attr_present' => __('attr.present'),
            'attr_absent' => __('attr.absent'),
            'attr_completed' => __('attr.completed'),
            'attr_not_completed' => __('attr.not_completed'),
            'attr_male' => __('attr.male'),
            'attr_female' => __('attr.female'),
            'Filter' => __('translation.Filter'),
            'export' => __('translation.export'),
            'total_students' => __('translation.total_students'),
            'homework' => __('translation.homework'),
            'completed' => __('translation.completed'),
            'missing' => __('translation.missing'),
            'rate' => __('translation.rate'),
            'total' => __('translation.total'),
            'actions' => __('translation.actions'),
            'show' => __('translation.show'),
            'search' => __('translation.search'),
            'no_data' => __('translation.no_data'),
            'all_teachers' => __('translation.all_teachers'),
            'all_groups' => __('translation.all_groups'),
            'users_select_teacher' => __('translation.users.select_teacher'),
            'groups_select_group' => __('translation.groups.select_group'),
            'attr_optional_date' => __('attr.optionalDate'),
            'attr_save' => __('attr.save'),
            'attr_cancel' => __('attr.cancel'),
            'clear_filters' => __('translation.clear_filters'),
            'loading' => __('translation.loading'),
            'group_attendances' => __('translation.group_attendances'),
            'total_students_count' => __('translation.total_students_count'),
            'comers' => __('translation.comers'),
            'not_comers' => __('translation.not_comers'),
            'percentage' => __('translation.percentage'),
            'select_date' => __('translation.reports.select_date'),
            'no_groups' => __('translation.groups.no_groups'),
            'telegram_connection_overview' => __('translation.telegram.connection_overview'),
            'telegram_connected' => __('translation.telegram.connected'),
            'telegram_not_connected' => __('translation.telegram.not_connected'),
            'telegram_connection_rate' => __('translation.telegram.connection_rate'),
            'telegram_top_connected_groups' => __('translation.telegram.top_connected_groups'),
            'telegram_needs_attention_groups' => __('translation.telegram.needs_attention_groups'),
            'telegram_groups_detail' => __('translation.telegram.groups_detail'),
            'telegram_excellent' => __('translation.telegram.excellent'),
            'telegram_good' => __('translation.telegram.good'),
            'telegram_poor' => __('translation.telegram.poor'),
            'telegram_critical' => __('translation.telegram.critical'),
            'telegram_id' => __('translation.telegram.id'),
            'telegram_connection_status' => __('translation.telegram.connection_status'),
            'telegram_students_connection_list' => __('translation.telegram.students_connection_list'),
            'btn_show' => __('translation.btn.show'),
            'btn_cancel' => __('translation.btn.cancel'),
            'groups_no_students' => __('translation.groups.no_students'),
            'status' => __('translation.status'),
            'user_calls_report' => __('translation.user_calls_report'),
            'name' => __('translation.name'),
            'answered' => __('translation.calls.answered'),
            'not_answered' => __('translation.calls.not_answered'),
            'busy' => __('translation.calls.busy'),
            'no_answer' => __('translation.calls.no_answer'),
            'total_calls' => __('translation.calls.total_calls'),
            'total_duration' => __('translation.calls.call_duration'),
            'students' => __('translation.students.students'),
            'leads' => __('translation.leads.leads'),
            'close' => __('translation.close'),
            'call_statistics' => __('translation.calls.call_statistics'),
        ];
    }

    private function formatDateRange(?string $from, ?string $to): string
    {
        $from = $from ? \Carbon\Carbon::parse($from)->format('Y.m.d') : now()->subDays(30)->format('Y.m.d');
        $to = $to ? \Carbon\Carbon::parse($to)->format('Y.m.d') : now()->format('Y.m.d');

        return "{$from} - {$to}";
    }

    private function normalizeDateInput(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $trimmedValue = trim($value);

        if ($trimmedValue === '' || in_array(strtolower($trimmedValue), ['null', 'undefined'], true)) {
            return null;
        }

        return $trimmedValue;
    }
}
