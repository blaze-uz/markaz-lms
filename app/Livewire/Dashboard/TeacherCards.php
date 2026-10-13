<?php

namespace App\Livewire\Dashboard;

use App\Models\Student;
use Livewire\Component;

class TeacherCards extends Component
{
    public $groupsCount, $studentsCount, $balance;

    public function mount()
    {
        $this->getData();
    }

    public function render()
    {
        return view('livewire.dashboard.teacher-cards');
    }

    private function getData()
    {
        $teacher = auth()->user();
        $this->groupsCount = $teacher->groups()->count();
        $this->studentsCount = Student::whereHas('groups', function ($query) use ($teacher) {
            $query->whereIn('group_id', $teacher->groups()->pluck('id'));
        })->count();
        $this->balance = $teacher->account?->balance ?? 0;
    }
}
