<?php

namespace App\Livewire\Attendance;

use App\Models\Attendance;
use DateTime;
use Illuminate\Support\Carbon;
use Livewire\Component;

class AttendanceTable extends Component
{
    public $groupId;
    public $date;

    public function mount($groupId): void
    {
        $this->groupId = $groupId;
        $this->getData();
    }

    public function render()
    {
        $this->validateDateFormat();
        $attendances = Attendance::with(['group', 'student' => function ($query) {
            $query->withTrashed();
        }])->where('group_id', $this->groupId)->whereDate('date', $this->date)->get();
        return view('livewire.attendance.attendance-table', compact('attendances'));
    }

    private function getData(): void
    {
        $this->date = Carbon::today()->format('Y-m-d');
    }

    private function validateDateFormat(): void
    {
        $d = DateTime::createFromFormat('Y-m-d', $this->date);
        if ($d && $d->format('Y-m-d') === $this->date) {
            $this->date = Carbon::parse($this->date)->format('Y-m-d');
        } else {
            $this->date = Carbon::today()->format('Y-m-d');
        }
    }
}
