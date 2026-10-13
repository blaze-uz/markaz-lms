<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidTelegramInitData;
use App\Http\Middleware\AuthenticateStudentWebApp;
use App\Models\Settings;
use App\Models\Student;
use App\Services\StudentTelegramWebAppService;
use App\Services\TelegramInitDataVerifier;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class StudentTelegramWebAppController extends Controller
{
    private StudentTelegramWebAppService $studentTelegramWebAppService;

    public function __construct(StudentTelegramWebAppService $studentTelegramWebAppService)
    {
        $this->studentTelegramWebAppService = $studentTelegramWebAppService;
    }

    private function getLogoUrl()
    {
        return Cache::remember('logo_url', now()->addHours(1), function () {
            $settings = Settings::where('key', 'logo')->first();
            $appearance = request()->cookie('appearance', 'system');
            $isDark = $appearance === 'dark' || ($appearance === 'system' && request()->header('Sec-CH-Prefers-Color-Scheme') === 'dark');

            return $settings && $settings->value != ''
                ? $settings->getFirstMediaUrl()
                : asset($isDark ? 'images/markaz-lms.svg' : 'images/markaz-lms.svg');
        });
    }

    public function index()
    {
        $settings = $this->studentTelegramWebAppService->getToken();

        return view('admin.settings.telegram.student-webapp', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'bot_token' => 'required|string|min:45|max:55',
        ]);

        try {
            $this->studentTelegramWebAppService->updateToken($request->all());

            return redirect()
                ->back()
                ->with('success_alert', __('translation.settings_saved'));
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Where the bot's "open app" button lands, the ones already sent included.
     * The Telegram id in the path no longer signs anyone in: it rides along to
     * the page as a claim, and the page asks Telegram who is really there.
     */
    public function login(Request $request, string $telegram_id)
    {
        $next = $this->safeNext($request->query('next'));

        parse_str((string) parse_url($next, PHP_URL_QUERY), $query);
        $query['telegram_id'] = $telegram_id;

        return redirect()->to(parse_url($next, PHP_URL_PATH).'?'.http_build_query($query));
    }

    /**
     * The mini-app posts Telegram's initData here once per session. Only data
     * signed with the student bot's token is accepted, and the student is the
     * one bound to the Telegram user inside it, never a request parameter.
     */
    public function authenticate(Request $request, TelegramInitDataVerifier $verifier)
    {
        $next = $this->safeNext($request->input('next'));
        $initData = $request->input('init_data');

        try {
            $data = $verifier->verify(is_string($initData) ? $initData : '', (string) $this->studentTelegramWebAppService->botToken());
        } catch (InvalidTelegramInitData $e) {
            info('Student web app: initData rejected: '.$e->getMessage());

            return $this->refuse($request, $next, "Telegram ma'lumotlari tasdiqlanmadi. Ilovani yopib, bot orqali qayta oching.");
        }

        $telegramId = (string) $data['user']['id'];

        // A link that names a student must name the one Telegram vouched for.
        parse_str((string) parse_url($next, PHP_URL_QUERY), $query);
        if (! AuthenticateStudentWebApp::claimMatches($query['telegram_id'] ?? null, $telegramId)) {
            return $this->refuse($request, $next, 'Bu havola boshqa Telegram akkaunt uchun. Ilovani o\'z botingiz orqali oching.');
        }

        if (! $this->studentTelegramWebAppService->getStudentByTelegramId($telegramId)) {
            return $this->refuse($request, $next, "Bu Telegram akkaunt o'quvchiga bog'lanmagan. Botda /start ni bosib, ro'yxatdan o'ting.");
        }

        $request->session()->regenerate();
        $request->session()->put(AuthenticateStudentWebApp::SESSION_KEY, $telegramId);

        return redirect()->to($next);
    }

    public function dashboard(Request $request)
    {
        $student = $this->student($request);

        $groups = Cache::remember("student.{$student->id}.groups", now()->addHour(), function () use ($student) {
            return $this->studentTelegramWebAppService->getStudentGroups($student);
        });

        // Cache today's group time for 1 hour since it's time-sensitive
        $todays_group_time = Cache::remember("student.{$student->id}.todays_group_time", now()->addHour(), function () use ($student) {
            return $this->studentTelegramWebAppService->getTodaysGroupTime($student);
        });

        $attendance_percentage = Cache::remember("student.{$student->id}.attendance_percentage", now()->addHour(), function () use ($student) {
            return $this->studentTelegramWebAppService->getAttendancePercentage($student);
        });

        return Inertia::render('Students/Index', [
            'settings' => [
                'logo_url' => $this->getLogoUrl(),
            ],
            'student' => $student,
            'groups' => $groups,
            'todays_group_time' => $todays_group_time,
            'attendance_percentage' => $attendance_percentage,
        ]);
    }

    public function attendance(Request $request)
    {
        $student = $this->student($request);

        $groups = Cache::remember("student.{$student->id}.groups", now()->addHour(), function () use ($student) {
            return $this->studentTelegramWebAppService->getStudentGroups($student);
        });

        if ($groups->isEmpty()) {
            return redirect()->route('twa.dashboard');
        }

        $selectedGroupId = $request->group_id ?? $groups->first()->id;
        $period = $request->period ?? '6_months';

        $attendanceData = Cache::remember(
            "student.{$student->id}.attendance.{$selectedGroupId}.{$period}",
            now()->addHour(),
            function () use ($student, $selectedGroupId, $period) {
                return $this->studentTelegramWebAppService->getAttendanceHeatmapData(
                    $student->id,
                    $selectedGroupId,
                    $period
                );
            }
        );

        return Inertia::render('Students/Attendance', [
            'student' => $student,
            'groups' => $groups,
            'selectedGroupId' => $selectedGroupId,
            'attendanceData' => $attendanceData,
            'settings' => [
                'logo_url' => $this->getLogoUrl(),
            ],
        ]);
    }

    public function settings(Request $request)
    {
        $student = $this->student($request);
        $telegraphChat = $this->studentTelegramWebAppService->getTelegraphChat($request->attributes->get('student_telegram_id'));

        return Inertia::render('Students/Settings', [
            'settings' => [
                'logo_url' => $this->getLogoUrl(),
            ],
            'student' => $student,
            'telegraphChat' => $telegraphChat,
        ]);
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'display_name' => ['required', 'string', 'max:255'],
        ]);

        $telegraphChat = $this->studentTelegramWebAppService->getTelegraphChat($request->attributes->get('student_telegram_id'));

        if (! $telegraphChat) {
            return back()->withErrors(['display_name' => 'Telegram foydalanuvchi topilmadi']);
        }

        // display_name is not fillable on Telegraph's model, so update() used to drop it.
        $telegraphChat->forceFill([
            'display_name' => $request->display_name,
        ])->save();

        return back()->with('success', 'Profil yangilandi');
    }

    public function payments(Request $request)
    {
        $student = $this->student($request);

        $payments = $student->payments()
            ->with(['payment_type', 'student_group_payment.group', 'creator'])
            ->latest()
            ->get()
            ->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'amount' => $payment->amount,
                    'date' => $payment->date ? Carbon::parse($payment->date)->format('d.m.Y H:i') : $payment->created_at->format('d.m.Y H:i'),
                    'payment_type' => $payment->payment_type?->name ?? 'Noma\'lum',
                    'group_name' => $payment->student_group_payment?->group?->name ?? null,
                    'comment' => $payment->comment,
                    'transaction_id' => $payment->transaction_id,
                    'has_refund' => $payment->refunds()->exists(),
                    'is_for_debt' => $payment->student_group_payment_id !== null,
                    'debt_range' => $payment->student_group_payment?->range ?? null,
                ];
            });

        $debts = \App\Models\StudentGroupPayment::where('student_id', $student->id)
            ->with('group')
            ->latest()
            ->get()
            ->map(function ($debt) {
                return [
                    'id' => $debt->id,
                    'group_name' => $debt->group?->name ?? 'Guruh topilmadi',
                    'total_amount' => $debt->amount,
                    'left_amount' => $debt->left_amount,
                    'paid_amount' => $debt->amount - $debt->left_amount,
                    'is_paid' => $debt->left_amount == 0,
                    'range' => $debt->range,
                    'date' => $debt->date ? Carbon::parse($debt->date)->format('d.m.Y') : null,
                ];
            });

        $balance = $student->balance;

        return inertia('Students/Payments', [
            'payments' => $payments,
            'debts' => $debts,
            'balance' => $balance,
            'settings' => [
                'logo_url' => $this->getLogoUrl(),
            ],
        ]);
    }

    /**
     * The student AuthenticateStudentWebApp verified through Telegram.
     */
    protected function student(Request $request): Student
    {
        $student = $request->attributes->get('student');

        if (! $student instanceof Student) {
            abort(404, 'Student not found');
        }

        return $student;
    }

    /**
     * Where to land after signing in: a page of the mini-app, never another site.
     */
    protected function safeNext(mixed $next): string
    {
        if (is_string($next) && preg_match('#^/twa(?:[/?]|\z)#', $next)) {
            return $next;
        }

        return route('twa.dashboard', [], false);
    }

    protected function refuse(Request $request, string $next, string $message)
    {
        return Inertia::render('Students/Authenticate', [
            'next' => $next,
            'error' => $message,
        ])->toResponse($request)->setStatusCode(403);
    }
}
