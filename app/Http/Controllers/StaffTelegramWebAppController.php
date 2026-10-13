<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StaffTelegramWebAppController extends Controller
{
    public function dashboard(Request $request)
{
        $user = User::where('telegram_id', $request->telegram_id)->firstOrFail();

        return Inertia::render('Staff/Dashboard', [
            'user' => $user,
        ]);
    }

    public function attendance(Request $request)
    {
        $user = User::where('telegram_id', $request->telegram_id)->firstOrFail();

        return Inertia::render('Staff/Attendance', [
            'user' => $user,
        ]);
    }

    public function reports(Request $request)
    {
        $user = User::where('telegram_id', $request->telegram_id)->firstOrFail();

        return Inertia::render('Staff/Reports', [
            'user' => $user,
        ]);
    }

    public function settings(Request $request)
    {
        $user = User::where('telegram_id', $request->telegram_id)->firstOrFail();

        return Inertia::render('Staff/Settings', [
            'user' => $user,
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = User::where('telegram_id', $request->telegram_id)->firstOrFail();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'required|string|max:255|unique:users,phone,' . $user->id,
        ]);

        $user->update($validated);

        return back()->with('success', 'Profile updated successfully');
    }
}
