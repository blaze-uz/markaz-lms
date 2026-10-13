<?php

namespace App\Http\Controllers;

use App\Http\Requests\Group\AttendanceRequest;
use App\Models\ValidationSettings;
use App\Services\AttendanceService;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public $service;

    public function __construct()
    {
        $this->service = new AttendanceService();
    }

    public function show(Request $request, $group_id)
    {
        $data = $request->validate([
            'date' => 'nullable|date|date_format:Y-m-d',
        ]);
        $item = $this->service->getGroup($group_id, ['students', 'attendanceSettings']);
        $attendancesExists = $item->attendances()->where('date', $data['date'] ?? date('Y-m-d'))->exists();
        $students = $item->students;
        $date = $data['date'] ?? date('Y-m-d');
        if (! $students->count()) {
            session()->flash('error_alert', __('translation.groups.students_not_found'));

            return redirect()->back();
        }
        if ($attendancesExists) {
            return redirect()->route('admin.attendance.edit', [
                'group' => $group_id,
                'date' => $data['date'] ?? date('Y-m-d'),
            ]);
        }
        $validationSettings = ValidationSettings::where('group', 'attendance')->get()->keyBy('name');

        return view('admin.groups.attendances.attendance', [
            'item' => $item,
            'students' => $students,
            'date' => $date,
            'validationSettings' => $validationSettings,
        ]);
    }

    public function store(AttendanceRequest $request, $group_id)
    {
        $this->service->store($request->validated(), $group_id);
        session()->flash('success_alert', __('translation.alert_messages.attendance'));

        return redirect()->route('admin.groups.show', $group_id);
    }

    public function edit(Request $request, $group_id)
    {
        $date = $request->input('date') ?? date('Y-m-d');
        $item = $this->service->getGroup($group_id, ['students']);
        $attendances = $this->service->getAttendances($date, $group_id);
        $validationSettings = ValidationSettings::where('group', 'attendance')->get()->keyBy('name');

        return view('admin.groups.attendances.attendance-edit', compact('attendances', 'item', 'date', 'validationSettings'));
    }

    public function update(AttendanceRequest $request, $group_id)
    {
        $this->service->update($request->validated(), $group_id);
        session()->flash('success_alert', __('translation.alert_messages.attendance_updated'));

        return redirect()->route('admin.groups.show', $group_id);
    }
}
