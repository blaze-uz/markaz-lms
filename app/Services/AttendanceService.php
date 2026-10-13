<?php

namespace App\Services;

use App\Jobs\CalculateDebtForStudent;
use App\Models\Attendance;
use App\Models\Group;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AttendanceService
{
    public function getGroup($group_id, $relations = [])
    {
        if (! is_numeric($group_id)) {
            abort(404, 'Invalid ID');
        }

        return Group::with($relations)->findOrFail($group_id);
    }

    public function store(array $data, $group_id): void
    {
        DB::beginTransaction();
        try {
            Log::info('AttendanceService store data:', $data);
            $student_ids = array_keys($data['status']);
            $group = $this->getGroup($group_id, ['attendanceSettings']);

            // Convert date from dd.mm.yyyy to Y-m-d if needed
            if (isset($data['date']) && str_contains($data['date'], '.')) {
                $date = Carbon::createFromFormat('d.m.Y', $data['date'])->format('Y-m-d');
            } else {
                $date = $data['date'] ?? date('Y-m-d');
            }

            $exists = $this->getAttendances($date, $group_id)->count();
            if ($exists) {
                // Nothing was written, but the transaction above is still open: returning
                // without closing it left it pending until the request ended, so anything
                // the rest of the request wrote was quietly rolled back with it.
                DB::rollBack();

                return;
            }

            $notify_time = null;
            $is_notified = null;
            if ($date == date('Y-m-d')) {
                if ($group->attendanceSettings) {
                    $notify_time = $this->getNotifyTime($group_id);
                    if ($group->attendanceSettings->auto_send && $notify_time) {
                        $is_notified = false;
                    }
                }
            }

            foreach ($student_ids as $student_id) {
                if ($data['status'][$student_id] !== 'present' && $data['status'][$student_id] !== 'absent') {
                    if ($data['status'][$student_id] === true) {
                        $data['status'][$student_id] = 'present';
                    } else {
                        $data['status'][$student_id] = 'absent';
                    }
                }

                Attendance::create([
                    'student_id' => $student_id,
                    'group_id' => $group_id,
                    'status' => $data['status'][$student_id],
                    'comment' => ! empty($data['comment'][$student_id]) ? $data['comment'][$student_id] : null,
                    'theme' => ! empty($data['theme']) ? $data['theme'] : null,
                    'score' => ! empty($data['score'][$student_id]) ? $data['score'][$student_id] : null,
                    'homework' => isset($data['homework'][$student_id]) ? (bool) $data['homework'][$student_id] : false,
                    'date' => $date,
                    'notify_time' => $notify_time,
                    'is_notified' => $is_notified,
                ]);
            }

            dispatch(new CalculateDebtForStudent([$this->getGroup($group_id)]));

            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
            throw new Exception($exception->getMessage());
        }
    }

    public function getAttendances($date, $group_id): \Illuminate\Database\Eloquent\Collection|array
    {
        return Attendance::with('student', 'group')
            ->where('group_id', $group_id)
            ->whereDate('date', $date)
            ->get();
    }

    public function update(array $data, $group_id): void
    {
        DB::beginTransaction();
        try {
            Log::info('AttendanceService update data:', $data);
            $attendance_ids = array_keys($data['status']);
            $group = $this->getGroup($group_id);

            // Convert date from dd.mm.yyyy to Y-m-d if needed
            if (isset($data['date']) && str_contains($data['date'], '.')) {
                $date = Carbon::createFromFormat('d.m.Y', $data['date'])->format('Y-m-d');
            } else {
                $date = $data['date'] ?? date('Y-m-d');
            }

            foreach ($attendance_ids as $attendance_id) {
                // Convert boolean status to string format
                $status = $data['status'][$attendance_id];
                if ($status !== 'present' && $status !== 'absent') {
                    $status = $status === true ? 'present' : 'absent';
                }

                Attendance::where('id', $attendance_id)
                    ->where('group_id', $group_id)
                    ->whereDate('date', $date)
                    ->update([
                        'status' => $status,
                        'comment' => ! empty($data['comment'][$attendance_id]) ? $data['comment'][$attendance_id] : null,
                        'theme' => ! empty($data['theme']) ? $data['theme'] : null,
                        'score' => ! empty($data['score'][$attendance_id]) ? $data['score'][$attendance_id] : null,
                        'homework' => isset($data['homework'][$attendance_id]) ? (bool) $data['homework'][$attendance_id] : false,
                    ]);
            }

            dispatch(new CalculateDebtForStudent([$group]));

            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
            throw new Exception($exception->getMessage());
        }
    }

    public function storeTelegram(array $data, $group_id): void
    {
        DB::beginTransaction();
        try {
            Log::info('AttendanceService storeTelegram data:', $data);
            $student_ids = array_keys($data['status']);
            $group = $this->getGroup($group_id, ['attendanceSettings']);

            // Convert date from dd.mm.yyyy to Y-m-d if needed
            if (isset($data['date']) && str_contains($data['date'], '.')) {
                $date = Carbon::createFromFormat('d.m.Y', $data['date'])->format('Y-m-d');
            } else {
                $date = $data['date'] ?? date('Y-m-d');
            }

            $exists = $this->getAttendances($date, $group_id)->count();
            if ($exists) {
                // Nothing was written, but the transaction above is still open: returning
                // without closing it left it pending until the request ended, so anything
                // the rest of the request wrote was quietly rolled back with it.
                DB::rollBack();

                return;
            }

            $notify_time = null;
            $is_notified = null;
            if ($date == date('Y-m-d')) {
                if ($group->attendanceSettings) {
                    $notify_time = $this->getNotifyTime($group_id);
                    if ($group->attendanceSettings->auto_send && $notify_time) {
                        $is_notified = false;
                    }
                }
            }

            foreach ($student_ids as $student_id) {
                // Convert boolean status to string format for Telegram data
                $status = $data['status'][$student_id];
                if ($status !== 'present' && $status !== 'absent') {
                    $status = $status === true ? 'present' : 'absent';
                }

                Attendance::create([
                    'student_id' => $student_id,
                    'group_id' => $group_id,
                    'status' => $status,
                    'comment' => ! empty($data['comment'][$student_id]) ? $data['comment'][$student_id] : null,
                    'theme' => ! empty($data['theme']) ? $data['theme'] : null,
                    'score' => ! empty($data['score'][$student_id]) ? $data['score'][$student_id] : null,
                    'homework' => isset($data['homework'][$student_id]) ? (bool) $data['homework'][$student_id] : false,
                    'date' => $date,
                    'notify_time' => $notify_time,
                    'is_notified' => $is_notified,
                ]);
            }

            dispatch(new CalculateDebtForStudent([$this->getGroup($group_id)]));

            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
            throw new Exception($exception->getMessage());
        }
    }

    public function updateTelegram(array $data, $group_id): void
    {
        DB::beginTransaction();
        try {
            Log::info('AttendanceService updateTelegram data:', $data);
            $student_ids = array_keys($data['status']);
            $group = $this->getGroup($group_id);

            // Convert date from dd.mm.yyyy to Y-m-d if needed
            if (isset($data['date']) && str_contains($data['date'], '.')) {
                $date = Carbon::createFromFormat('d.m.Y', $data['date'])->format('Y-m-d');
            } else {
                $date = $data['date'] ?? date('Y-m-d');
            }

            foreach ($student_ids as $student_id) {
                // Convert boolean status to string format
                $status = $data['status'][$student_id];
                if ($status !== 'present' && $status !== 'absent') {
                    $status = $status === true ? 'present' : 'absent';
                }

                Attendance::where('student_id', $student_id)
                    ->where('group_id', $group_id)
                    ->whereDate('date', $date)
                    ->update([
                        'status' => $status,
                        'comment' => ! empty($data['comment'][$student_id]) ? $data['comment'][$student_id] : null,
                        'theme' => ! empty($data['theme']) ? $data['theme'] : null,
                        'score' => ! empty($data['score'][$student_id]) ? $data['score'][$student_id] : null,
                        'homework' => isset($data['homework'][$student_id]) ? (bool) $data['homework'][$student_id] : false,
                    ]);
            }

            dispatch(new CalculateDebtForStudent([$group]));

            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
            throw new Exception($exception->getMessage());
        }
    }

    private function getNotifyTime($group_id)
    {
        $group = $this->getGroup($group_id, ['groupTimes', 'attendanceSettings']);
        $groupTimes = $group->groupTimes()->where('name', strtolower(Carbon::today()->format('l')))->first();
        if ($groupTimes && $group->attendanceSettings) {
            return Carbon::parse($groupTimes->end)
                ->addMinutes($group->attendanceSettings->after_minutes)
                ->format('H:i');
        }

        return null;
    }
}
