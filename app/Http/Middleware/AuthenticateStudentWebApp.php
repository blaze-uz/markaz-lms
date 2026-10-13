<?php

namespace App\Http\Middleware;

use App\Models\Student;
use App\Services\StudentTelegramWebAppService;
use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

/**
 * Lets a request into the student Telegram mini-app (/twa) only for the
 * student Telegram vouched for.
 *
 * The app used to load whichever student a `telegram_id` query parameter
 * named, so anyone who knew a student's or parent's Telegram id could read
 * their groups, attendance and payments. Now the mini-app proves who is using
 * it by posting Telegram's signed initData to /twa/auth, and only that
 * verified Telegram id is kept in the session. A `telegram_id` in the request
 * is just a claim: when it names someone else, a page view asks Telegram
 * again and a write is refused.
 */
class AuthenticateStudentWebApp
{
    public const SESSION_KEY = 'student_webapp_telegram_id';

    public function __construct(protected StudentTelegramWebAppService $service)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $telegramId = (string) $request->session()->get(self::SESSION_KEY, '');
        $student = $telegramId !== '' ? $this->service->getStudentByTelegramId($telegramId) : null;

        if ($student instanceof Student && self::claimMatches($request->input('telegram_id'), $telegramId)) {
            $request->attributes->set('student', $student);
            $request->attributes->set('student_telegram_id', $telegramId);

            return $next($request);
        }

        if (! $request->isMethod('GET') && ! $request->isMethod('HEAD')) {
            abort($student ? 403 : 401);
        }

        return Inertia::render('Students/Authenticate', [
            'next' => $request->getRequestUri(),
            'error' => null,
        ])->toResponse($request);
    }

    /**
     * A request may leave telegram_id out; when it names one, it must be the verified one.
     */
    public static function claimMatches(mixed $claimed, string $telegramId): bool
    {
        return blank($claimed) || (is_scalar($claimed) && (string) $claimed === $telegramId);
    }
}
