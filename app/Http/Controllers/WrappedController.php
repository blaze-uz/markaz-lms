<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Group;
use App\Models\Student;
use App\Models\User;
use App\Services\WrappedService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WrappedController extends Controller
{
    public function __construct(
        private readonly WrappedService $wrappedService
    ) {
    }

    public function show(Request $request, ?int $year = null): \Illuminate\Contracts\View\View
    {
        $user = auth()->user();
        $year = $year ?? now()->year;

        $wrappedData = $this->wrappedService->getWrappedData($user, $year);

        return view('admin.wrapped.index', [
            'wrapped' => $wrappedData,
            'availableYears' => $this->getAvailableYears($user),
            'currentYear' => $year,
        ]);
    }

    private function getAvailableYears(User $user): array
    {
        $currentYear = now()->year;
        $years = [];

        if ($user->isAdmin()) {
            $earliestYear = Student::min(DB::raw('EXTRACT(YEAR FROM created_at)')) ?? $currentYear;
        } elseif ($user->isTeacher()) {
            $earliestYear = Group::where('teacher_id', $user->id)
                ->min(DB::raw('EXTRACT(YEAR FROM created_at)')) ?? $currentYear;
        } else {
            $student = Student::where('phone', $user->phone)->first();
            if ($student) {
                $earliestYear = Attendance::where('student_id', $student->id)
                    ->min(DB::raw('EXTRACT(YEAR FROM date)')) ?? $currentYear;
            } else {
                $earliestYear = $currentYear;
            }
        }

        for ($year = $currentYear; $year >= (int) $earliestYear; $year--) {
            $years[] = $year;
        }

        return $years;
    }
}
