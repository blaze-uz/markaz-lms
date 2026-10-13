<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Inertia\Inertia;
use Inertia\Response;

class AttendanceRecordController extends Controller
{
    public function show(Attendance $attendance): Response
    {
        $user = auth()->user();
        abort_unless(
            $user && ($user->can('group-attendance') || $user->can('report-educational')),
            403
        );

        $this->assertAttendanceWithinBranch($attendance);

        $attendance->load(['student', 'group']);

        Inertia::setRootView('layouts.admin');

        return Inertia::render('Attendance/Show', [
            'record' => $this->transformRecord($attendance),
            'labels' => $this->labels(),
        ]);
    }

    private function assertAttendanceWithinBranch(Attendance $attendance): void
    {
        $branchId = auth()->user()?->current_branch_id;
        $student = $attendance->student;

        if (! $branchId || ! $student) {
            abort(403);
        }

        $inBranch = $student->branches()->where('branch_id', $branchId)->exists();

        abort_unless($inBranch, 403);
    }

    /**
     * @return array<string, mixed>
     */
    private function transformRecord(Attendance $attendance): array
    {
        $student = $attendance->student;
        $group = $attendance->group;
        $status = $attendance->status;
        $date = $attendance->date;
        $locale = app()->getLocale();

        $dateFormatted = $date
            ? $date->copy()->locale($locale)->translatedFormat('l, j F Y')
            : null;

        $notifyTimeFormatted = $attendance->notify_time
            ? $attendance->notify_time->copy()->locale($locale)->translatedFormat('j F Y, H:i')
            : null;

        $monthlyPaymentRaw = $attendance->monthly_payment;
        $monthlyPaymentFormatted = $monthlyPaymentRaw !== null
            ? number_format((float) $monthlyPaymentRaw, 2, '.', ' ').' '.__('translation.attendance_record.currency_suffix')
            : null;

        return [
            'id' => $attendance->id,
            'student' => $student ? [
                'id' => $student->id,
                'name' => trim($student->getFullName()),
                'url' => route('admin.students.show', $student),
            ] : null,
            'group' => $group ? [
                'id' => $group->id,
                'name' => $group->name,
                'url' => route('admin.groups.show', $group),
            ] : null,
            'date_iso' => $date?->toDateString(),
            'date_formatted' => $dateFormatted,
            'status' => $status?->value,
            'status_label' => $status?->getText(),
            'theme' => $attendance->theme,
            'score' => $attendance->score,
            'homework' => $attendance->homework,
            'comment' => $attendance->comment,
            'monthly_payment_formatted' => $monthlyPaymentFormatted,
            'is_notified' => (bool) $attendance->is_notified,
            'notify_time_formatted' => $notifyTimeFormatted,
        ];
    }

    /**
     * @return array<string, string>
     */
    private function labels(): array
    {
        return [
            'page_title' => __('translation.attendance_record.page_title'),
            'section_overview' => __('translation.attendance_record.section_overview'),
            'section_performance' => __('translation.attendance_record.section_performance'),
            'section_finance' => __('translation.attendance_record.section_finance'),
            'section_communication' => __('translation.attendance_record.section_communication'),
            'label_student' => __('translation.attendance_record.label_student'),
            'label_group' => __('translation.attendance_record.label_group'),
            'label_date' => __('translation.attendance_record.label_date'),
            'label_status' => __('translation.attendance_record.label_status'),
            'label_theme' => __('translation.attendance_record.label_theme'),
            'label_score' => __('translation.attendance_record.label_score'),
            'label_homework' => __('translation.attendance_record.label_homework'),
            'label_comment' => __('translation.attendance_record.label_comment'),
            'label_monthly_payment' => __('translation.attendance_record.label_monthly_payment'),
            'label_notification' => __('translation.attendance_record.label_notification'),
            'label_notify_time' => __('translation.attendance_record.label_notify_time'),
            'empty_no_comment' => __('translation.attendance_record.empty_no_comment'),
            'empty_theme' => __('translation.attendance_record.empty_theme'),
            'empty_score' => __('translation.attendance_record.empty_score'),
            'empty_payment' => __('translation.attendance_record.empty_payment'),
            'empty_notify_time' => __('translation.attendance_record.empty_notify_time'),
            'homework_done' => __('translation.attendance_record.homework_done'),
            'homework_not_done' => __('translation.attendance_record.homework_not_done'),
            'homework_unknown' => __('translation.attendance_record.homework_unknown'),
            'notified_yes' => __('translation.attendance_record.notified_yes'),
            'notified_no' => __('translation.attendance_record.notified_no'),
            'back' => __('translation.attendance_record.back'),
            'open_student' => __('translation.attendance_record.open_student'),
            'open_group' => __('translation.attendance_record.open_group'),
        ];
    }
}
