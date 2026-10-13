<?php

namespace App\Http\Controllers;

use App\Services\StaffTelegramBotService;
use Illuminate\Http\Request;

class TelegramWebAppController extends Controller
{
    protected $staffService;

    public function __construct(StaffTelegramBotService $staffService)
    {
        $this->staffService = $staffService;
    }

    public function login(Request $request, $telegram_id)
    {
        $next = $request->input('next');

        $user = $this->staffService->getUserByTelegramId($telegram_id);

        if (!$user) {
            abort(404, 'User not found');
        }

        if ($next) {
            return redirect($next);
        }

        return redirect()->route('inertia-teachers.index', ['telegram_id' => $telegram_id]);
    }
}
