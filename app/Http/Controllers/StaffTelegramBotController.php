<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidTelegramInitData;
use App\Http\Middleware\AuthenticateStaffWebApp;
use App\Http\Requests\Group\AttendanceRequest;
use App\Models\Group;
use App\Models\User;
use App\Services\AttendanceService;
use App\Services\StaffTelegramBotService;
use App\Services\TelegramInitDataVerifier;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class StaffTelegramBotController extends Controller
{
    protected $service;

    public function __construct(StaffTelegramBotService $service)
    {
        $this->service = $service;
    }

    public function loginView()
    {
        return Inertia::render('Staff/Login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'login' => 'required',
            'password' => 'required',
        ]);

        try {
            $this->service->login($data);

            return redirect()->route('inertia-teachers.index');
        } catch (ValidationException $e) {
            return back()->withErrors([
                'login' => $e->getMessage(),
            ]);
        }
    }

    /**
     * The mini-app posts Telegram's initData here once per session. Only data
     * signed with the staff bot's token is accepted, and the staff member is
     * the Telegram user inside it, never a request parameter.
     */
    public function authenticate(Request $request, TelegramInitDataVerifier $verifier)
    {
        $next = $this->safeNext($request->input('next'));
        $initData = $request->input('init_data');

        try {
            $data = $verifier->verify(is_string($initData) ? $initData : '', (string) $this->service->botToken());
        } catch (InvalidTelegramInitData $e) {
            info('Staff web app: initData rejected: '.$e->getMessage());

            return $this->refuse($request, $next, "Telegram ma'lumotlari tasdiqlanmadi. Ilovani yopib, bot orqali qayta oching.");
        }

        $telegramId = (string) $data['user']['id'];

        // A link that names a staff member must name the one Telegram vouched for.
        parse_str((string) parse_url($next, PHP_URL_QUERY), $query);
        if (! AuthenticateStaffWebApp::claimMatches($query['telegram_id'] ?? null, $telegramId)) {
            return $this->refuse($request, $next, 'Bu havola boshqa Telegram akkaunt uchun. Ilovani o\'z botingiz orqali oching.');
        }

        if (! $this->service->getUserByTelegramId($telegramId)) {
            return $this->refuse($request, $next, "Bu Telegram akkaunt xodim sifatida ro'yxatdan o'tmagan. Botda /start ni bosing.");
        }

        $request->session()->regenerate();
        $request->session()->put(AuthenticateStaffWebApp::SESSION_KEY, $telegramId);

        return redirect()->to($next);
    }

    public function index(Request $request)
    {
        $branch_id = $request->query('branch_id');
        $user = $this->staffUser($request);

        // Set specific branch if provided and user has access to it
        if ($branch_id) {
            $branch = $user->branches()->where('branch_id', $branch_id)->first();
            if ($branch) {
                $user->update(['current_branch_id' => $branch_id]);
                info("User {$user->id} switched to branch {$branch->name} (ID: {$branch_id}) via web app");
            }
        }

        // Ensure user has branch context set
        $this->service->ensureUserBranchContext($user);

        $groups = $this->service->getTodayGroups($user);

        return Inertia::render('Staff/Index', [
            'user' => $user,
            'groups' => $groups,
        ]);
    }

    public function groups(Request $request)
    {
        $branch_id = $request->query('branch_id');

        $user = $this->staffUser($request);

        // Set specific branch if provided and user has access to it
        if ($branch_id) {
            $branch = $user->branches()->where('branch_id', $branch_id)->first();
            if ($branch) {
                $user->update(['current_branch_id' => $branch_id]);
            }
        }

        // Ensure user has branch context set
        $this->service->ensureUserBranchContext($user);

        $groups = $this->service->getGroups($user);

        return Inertia::render('Staff/Groups', [
            'groups' => $groups,
        ]);
    }

    public function groupShow($id, Request $request)
    {
        $group = $this->staffGroup($request, $id);

        $students = $this->service->getGroupStudents($id);
        $selectedDate = $request->query('date', now()->format('Y-m-d'));
        $attendances = $this->service->getGroupAttendances($id, $selectedDate);
        $attendanceDates = $this->service->getGroupAttendanceDates($id);

        return Inertia::render('Staff/Group', [
            'group' => $group,
            'students' => $students,
            'attendances' => $attendances,
            'attendanceDates' => $attendanceDates,
            'selectedDate' => $selectedDate,
        ]);
    }

    public function groupAttendanceShow(Request $request, $id)
    {
        $group = $this->staffGroup($request, $id);

        $attendance_exists = $group->attendances()->where('date', now()->format('Y-m-d'))->exists();
        if ($attendance_exists) {
            return redirect()->route('inertia-teachers.groups.attendance.edit', $id);
        }
        $students = $this->service->getGroupStudents($id);
        $validationSettings = \App\Models\ValidationSettings::where('group', 'attendance')->get()->keyBy('name');

        return Inertia::render('Staff/GroupAttendance', [
            'group' => $group,
            'students' => $students,
            'validationSettings' => $validationSettings,
        ]);
    }

    public function groupAttendanceEdit(Request $request, $id)
    {
        $group = $this->staffGroup($request, $id);

        $students = $this->service->getGroupStudents($id);
        $attendanceService = new AttendanceService();
        $attendances = $attendanceService->getAttendances(now()->format('Y-m-d'), $id);

        // Format attendance data for the frontend
        $attendanceData = [];
        foreach ($attendances as $attendance) {
            $attendanceData[$attendance->student_id] = [
                'status' => $attendance->status,
                'comment' => $attendance->comment ?? '',
                'score' => $attendance->score ?? '',
                'homework' => (bool) ($attendance->homework ?? false),
            ];
        }

        $validationSettings = \App\Models\ValidationSettings::where('group', 'attendance')->get()->keyBy('name');

        return Inertia::render('Staff/GroupAttendanceEdit', [
            'group' => $group,
            'students' => $students,
            'attendance' => $attendanceData,
            'validationSettings' => $validationSettings,
        ]);
    }

    public function groupAttendanceUpdate(AttendanceRequest $request, $id)
    {
        $this->staffGroup($request, $id);

        $attendanceService = new AttendanceService();
        // Use Telegram-specific update method that handles student IDs
        $attendanceService->updateTelegram($request->validated(), $id);

        session()->flash('success', 'Saqlandi');

        return redirect()->route('inertia-teachers.index');
    }

    public function groupAttendanceStore(AttendanceRequest $request, $id)
    {
        $group = $this->staffGroup($request, $id);
        $attendance_exists = $group->attendances()->where('date', now()->format('Y-m-d'))->exists();
        if ($attendance_exists) {
            return redirect()->back()->with('error', 'Attendance already exists for this group');
        }

        $attendanceService = new AttendanceService();
        // Use Telegram-specific method that handles student IDs properly
        $attendanceService->storeTelegram($request->validated(), $id);

        session()->flash('success', 'Saqlandi');

        return redirect()->route('inertia-teachers.index');
    }

    public function settings(Request $request)
    {
        $branch_id = $request->query('branch_id');

        $user = $this->staffUser($request);

        // Set specific branch if provided and user has access to it
        if ($branch_id) {
            $branch = $user->branches()->where('branch_id', $branch_id)->first();
            if ($branch) {
                $user->update(['current_branch_id' => $branch_id]);
            }
        }

        // Ensure user has branch context set
        $this->service->ensureUserBranchContext($user);

        return Inertia::render('Staff/Settings', [
            'user' => $user,
        ]);
    }

    /**
     * The staff member AuthenticateStaffWebApp verified through Telegram.
     */
    protected function staffUser(Request $request): User
    {
        $user = $request->attributes->get('staff_user');

        if (! $user instanceof User) {
            abort(404, 'Staff user not found');
        }

        return $user;
    }

    protected function staffGroup(Request $request, $id): Group
    {
        $group = $this->service->getUserGroup($this->staffUser($request), $id);

        if (! $group) {
            abort(404, 'Group not found or not accessible');
        }

        return $group;
    }

    /**
     * Where to land after signing in: a page of the mini-app, never another site.
     */
    protected function safeNext(mixed $next): string
    {
        if (is_string($next) && preg_match('#^/inertia-teachers(?:[/?]|\z)#', $next)) {
            return $next;
        }

        return route('inertia-teachers.index', [], false);
    }

    protected function refuse(Request $request, string $next, string $message)
    {
        return Inertia::render('Staff/Authenticate', [
            'next' => $next,
            'error' => $message,
        ])->toResponse($request)->setStatusCode(403);
    }
}
