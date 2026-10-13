<?php

namespace App\Http\Middleware;

use App\Models\Attendance;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

class CheckAttendance
{
    public function handle(Request $request, Closure $next)
    {
        $groupId = Route::current()->parameter('group');
        $attendance = Attendance::where('group_id', $groupId)->latest()->first();
        if ($attendance && Carbon::parse($attendance->created_at)->format('Y-m-d') === Carbon::now()->format('Y-m-d')) {
            session()->flash('error_alert', __('translation.alert_messages.attendance_already_taken'));
            return redirect()->route('admin.groups.show', $groupId);
        } else {
            return $next($request);
        }
    }
}
