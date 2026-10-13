<?php

namespace App\Livewire\Report;

use App\Models\Group;
use Livewire\Component;

class GroupAttendance extends Component
{
    public $date, $search = '';
    public $branchId;

    public function mount()
    {
        $this->date = date('Y-m-d');
    }

    public function render()
    {
        $date = (isset($this->date) && $this->date) ? $this->date : now()->format('Y-m-d');
        $dayName = strtolower(dateFormat($date, 'l', 'en'));

        $groups = Group::query()
            ->where('state', 'active')
            ->whereDate('start_date', '<=', $date)
            ->whereHas('groupTimes', function ($query) use ($dayName) {
                $query->where('name', $dayName);
            })
            ->whereHas('attendances', function ($query) use ($date) {
                $query->where('date', $date);
            })
            ->where(function ($query) {
                if (!empty($this->search)) {
                    $query->where('name', 'ilike', "%{$this->search}%")
                        ->orWhereHas('course', function ($q) {
                            $q->where('name', 'ilike', "%{$this->search}%");
                        })
                        ->orWhereHas('teacher', function ($q) {
                            $q->where('name', 'ilike', "%{$this->search}%");
                        });
                }
            })
            ->with(['teacher', 'course', 'attendances' => function ($query) use ($date) {
                $query->where('date', $date)->select('id', 'group_id', 'student_id', 'date', 'status', 'theme');
            }])
            ->withCount('students')
            ->withCount([
                'attendances as comers' => function ($query) use ($date) {
                    $query->where('date', $date)
                        ->where('status', 'present');
                }
            ])
            ->withCount([
                'attendances as absent_count' => function ($query) use ($date) {
                    $query->where('date', $date)
                        ->where('status', 'absent');
                }
            ])
            ->withCount([
                'students as not_comers' => function ($query) use ($date) {
                    $query->whereDoesntHave('attendances', function ($q) use ($date) {
                        $q->where('date', $date);
                    });
                }
            ])
            ->orderBy('name')
            ->get();

        return view('livewire.report.group-attendance', compact('groups'));
    }
}
