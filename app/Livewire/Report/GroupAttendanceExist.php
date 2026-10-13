<?php

namespace App\Livewire\Report;

use App\Models\Group;
use Livewire\Component;

class GroupAttendanceExist extends Component
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
        $groups = Group::query()
            ->where('state', 'active')
            ->whereDate('start_date', '<=', $date)
            ->whereRelation('groupTimes', function ($query) use ($date) {
                $query->where('name', strtolower(dateFormat($date ?? now()->format('Y-m-d'), 'l', 'en')));
            })
            ->whereDoesntHave('attendances', function ($query) use ($date) {
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
            ->with('teacher')
            ->withCount('students')
            ->orderBy('name')
            ->get();
        return view('livewire.report.group-attendance-exist', compact('groups'));
    }
}
