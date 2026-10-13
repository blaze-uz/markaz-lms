<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Group;
use App\Models\Payment;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class WrappedService
{
    public function getWrappedData(User $user, ?int $year = null): array
    {
        $year = $year ?? now()->year;
        $startOfYear = Carbon::create($year, 1, 1)->startOfDay();
        $endOfYear = Carbon::create($year, 12, 31)->endOfDay();

        if ($user->isAdmin()) {
            return $this->getAdminWrapped($user, $startOfYear, $endOfYear, $year);
        }

        if ($user->isTeacher()) {
            return $this->getTeacherWrapped($user, $startOfYear, $endOfYear, $year);
        }

        return $this->getStudentWrapped($user, $startOfYear, $endOfYear, $year);
    }

    private function getAdminWrapped(User $user, Carbon $startOfYear, Carbon $endOfYear, int $year): array
    {
        $branchId = $user->current_branch_id;

        $totalStudents = Student::whereHas('branches', function ($query) use ($branchId) {
            if ($branchId) {
                $query->where('branch_id', $branchId);
            }
        })
            ->whereBetween('created_at', [$startOfYear, $endOfYear])
            ->count();

        $totalGroups = Group::whereBetween('created_at', [$startOfYear, $endOfYear])
            ->when($branchId, function ($query) use ($branchId) {
                $query->whereHas('branches', function ($q) use ($branchId) {
                    $q->where('branch_id', $branchId);
                });
            })
            ->count();

        $totalPayments = Payment::whereBetween('created_at', [$startOfYear, $endOfYear])
            ->when($branchId, function ($query) use ($branchId) {
                $query->whereHas('student.branches', function ($q) use ($branchId) {
                    $q->where('branch_id', $branchId);
                });
            })
            ->sum('amount');

        $totalAttendance = Attendance::whereBetween('date', [$startOfYear, $endOfYear])
            ->when($branchId, function ($query) use ($branchId) {
                $query->whereHas('group.branches', function ($q) use ($branchId) {
                    $q->where('branch_id', $branchId);
                });
            })
            ->count();

        $paymentsByMonth = Payment::whereBetween('created_at', [$startOfYear, $endOfYear])
            ->when($branchId, function ($query) use ($branchId) {
                $query->whereHas('student.branches', function ($q) use ($branchId) {
                    $q->where('branch_id', $branchId);
                });
            })
            ->selectRaw('EXTRACT(MONTH FROM created_at) as month, SUM(amount) as total')
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();

        $studentsByMonth = Student::whereHas('branches', function ($query) use ($branchId) {
            if ($branchId) {
                $query->where('branch_id', $branchId);
            }
        })
            ->whereBetween('created_at', [$startOfYear, $endOfYear])
            ->selectRaw('EXTRACT(MONTH FROM created_at) as month, COUNT(*) as count')
            ->groupBy('month')
            ->pluck('count', 'month')
            ->toArray();

        return [
            'user_type' => 'admin',
            'year' => $year,
            'user_name' => $user->name,
            'stats' => [
                'total_students' => $totalStudents,
                'total_groups' => $totalGroups,
                'total_payments' => round($totalPayments, 2),
                'total_attendance' => $totalAttendance,
            ],
            'charts' => [
                'payments_by_month' => $this->formatMonthlyData($paymentsByMonth),
                'students_by_month' => $this->formatMonthlyData($studentsByMonth),
            ],
        ];
    }

    private function getTeacherWrapped(User $user, Carbon $startOfYear, Carbon $endOfYear, int $year): array
    {
        $groups = $user->groups()
            ->whereBetween('created_at', [$startOfYear, $endOfYear])
            ->get();

        $totalGroups = $groups->count();
        $activeGroups = $groups->where('state', 'active')->count();
        $completedGroups = $groups->where('state', 'completed')->count();

        $totalStudents = DB::table('group_student')
            ->join('groups', 'group_student.group_id', '=', 'groups.id')
            ->where('groups.teacher_id', $user->id)
            ->whereBetween('group_student.created_at', [$startOfYear, $endOfYear])
            ->distinct('group_student.student_id')
            ->count('group_student.student_id');

        $totalAttendanceMarked = Attendance::whereHas('group', function ($query) use ($user) {
            $query->where('teacher_id', $user->id);
        })
            ->whereBetween('date', [$startOfYear, $endOfYear])
            ->count();

        $presentCount = Attendance::whereHas('group', function ($query) use ($user) {
            $query->where('teacher_id', $user->id);
        })
            ->whereBetween('date', [$startOfYear, $endOfYear])
            ->where('status', 'present')
            ->count();

        $attendanceByMonth = Attendance::whereHas('group', function ($query) use ($user) {
            $query->where('teacher_id', $user->id);
        })
            ->whereBetween('date', [$startOfYear, $endOfYear])
            ->selectRaw('EXTRACT(MONTH FROM date) as month, COUNT(*) as count')
            ->groupBy('month')
            ->pluck('count', 'month')
            ->toArray();

        $groupsByMonth = Group::where('teacher_id', $user->id)
            ->whereBetween('created_at', [$startOfYear, $endOfYear])
            ->selectRaw('EXTRACT(MONTH FROM created_at) as month, COUNT(*) as count')
            ->groupBy('month')
            ->pluck('count', 'month')
            ->toArray();

        $topCourse = DB::table('groups')
            ->where('teacher_id', $user->id)
            ->whereBetween('created_at', [$startOfYear, $endOfYear])
            ->join('courses', 'groups.course_id', '=', 'courses.id')
            ->select('courses.name', DB::raw('COUNT(*) as count'))
            ->groupBy('courses.id', 'courses.name')
            ->orderByDesc('count')
            ->first();

        return [
            'user_type' => 'teacher',
            'year' => $year,
            'user_name' => $user->name,
            'stats' => [
                'total_groups' => $totalGroups,
                'active_groups' => $activeGroups,
                'completed_groups' => $completedGroups,
                'total_students' => $totalStudents,
                'total_attendance_marked' => $totalAttendanceMarked,
                'present_count' => $presentCount,
                'top_course' => $topCourse?->name ?? 'N/A',
            ],
            'charts' => [
                'attendance_by_month' => $this->formatMonthlyData($attendanceByMonth),
                'groups_by_month' => $this->formatMonthlyData($groupsByMonth),
            ],
        ];
    }

    private function getStudentWrapped(User $user, Carbon $startOfYear, Carbon $endOfYear, int $year): array
    {
        $student = Student::where('phone', $user->phone)->orWhere('parent_phone', $user->phone)->first();

        if (! $student) {
            return [
                'user_type' => 'student',
                'year' => $year,
                'user_name' => $user->name,
                'stats' => [],
                'charts' => [],
                'message' => 'No student record found',
            ];
        }

        $groups = $student->groups()
            ->whereBetween('group_student.created_at', [$startOfYear, $endOfYear])
            ->get();

        $totalGroups = $groups->count();
        $completedGroups = $student->completedGroups()
            ->whereBetween('group_student.created_at', [$startOfYear, $endOfYear])
            ->count();

        $totalAttendance = Attendance::where('student_id', $student->id)
            ->whereBetween('date', [$startOfYear, $endOfYear])
            ->count();

        $presentCount = Attendance::where('student_id', $student->id)
            ->whereBetween('date', [$startOfYear, $endOfYear])
            ->where('status', 'present')
            ->count();

        $totalPayments = Payment::where('student_id', $student->id)
            ->whereBetween('created_at', [$startOfYear, $endOfYear])
            ->sum('amount');

        $attendanceByMonth = Attendance::where('student_id', $student->id)
            ->whereBetween('date', [$startOfYear, $endOfYear])
            ->selectRaw('EXTRACT(MONTH FROM date) as month, COUNT(*) as count')
            ->groupBy('month')
            ->pluck('count', 'month')
            ->toArray();

        $paymentsByMonth = Payment::where('student_id', $student->id)
            ->whereBetween('created_at', [$startOfYear, $endOfYear])
            ->selectRaw('EXTRACT(MONTH FROM created_at) as month, SUM(amount) as total')
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();

        $topCourse = DB::table('group_student')
            ->where('student_id', $student->id)
            ->whereBetween('group_student.created_at', [$startOfYear, $endOfYear])
            ->join('groups', 'group_student.group_id', '=', 'groups.id')
            ->join('courses', 'groups.course_id', '=', 'courses.id')
            ->select('courses.name', DB::raw('COUNT(*) as count'))
            ->groupBy('courses.id', 'courses.name')
            ->orderByDesc('count')
            ->first();

        $averageScore = Attendance::where('student_id', $student->id)
            ->whereBetween('date', [$startOfYear, $endOfYear])
            ->whereNotNull('score')
            ->avg('score');

        return [
            'user_type' => 'student',
            'year' => $year,
            'user_name' => $user->name,
            'student_name' => $student->getFullName(),
            'stats' => [
                'total_groups' => $totalGroups,
                'completed_groups' => $completedGroups,
                'total_attendance' => $totalAttendance,
                'present_count' => $presentCount,
                'attendance_percentage' => $totalAttendance > 0 ? round(($presentCount / $totalAttendance) * 100, 1) : 0,
                'total_payments' => round($totalPayments, 2),
                'average_score' => $averageScore ? round($averageScore, 1) : null,
                'top_course' => $topCourse?->name ?? 'N/A',
            ],
            'charts' => [
                'attendance_by_month' => $this->formatMonthlyData($attendanceByMonth),
                'payments_by_month' => $this->formatMonthlyData($paymentsByMonth),
            ],
        ];
    }

    private function formatMonthlyData(array $data): array
    {
        $formatted = [];
        for ($month = 1; $month <= 12; $month++) {
            $formatted[$month] = $data[$month] ?? 0;
        }

        return $formatted;
    }
}
