<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\StaffTelegramBotService;
use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

/**
 * Lets a request into the staff Telegram mini-app (inertia-teachers) only for
 * the staff member Telegram vouched for.
 *
 * The group used to load whichever staff member a `telegram_id` parameter
 * named, and the group pages needed nothing at all. Now the mini-app proves
 * who is using it by posting Telegram's signed initData to
 * inertia-teachers/auth, and only that verified Telegram id is kept in the
 * session. A `telegram_id` in the request is just a claim: when it names
 * someone else, a page view asks Telegram again and a write is refused.
 */
class AuthenticateStaffWebApp
{
    public const SESSION_KEY = 'staff_webapp_telegram_id';

    public function __construct(protected StaffTelegramBotService $service)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $telegramId = (string) $request->session()->get(self::SESSION_KEY, '');
        $user = $telegramId !== '' ? $this->service->getUserByTelegramId($telegramId) : null;

        if ($user instanceof User && self::claimMatches($request->input('telegram_id'), $telegramId)) {
            $request->attributes->set('staff_user', $user);
            $request->attributes->set('staff_telegram_id', $telegramId);

            return $next($request);
        }

        if (! $request->isMethod('GET') && ! $request->isMethod('HEAD')) {
            abort($user ? 403 : 401);
        }

        return Inertia::render('Staff/Authenticate', [
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
