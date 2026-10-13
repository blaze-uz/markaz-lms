<?php

namespace App\Livewire\Dashboard;

use App\Models\User;
use Livewire\Component;

class TeacherGroupChart extends Component
{
    public function render()
    {
        [$labels, $series] = $this->getData();
        return view('livewire.dashboard.teacher-group-chart', compact('labels', 'series'));
    }

    public function getData(): array
    {
        //get all teachers where role is teacher
        $teachers = User::with('groups')->role('Teacher')->get();

        $labels = [];
        $series = [];

        foreach ($teachers as $teacher) {
            $labels[] = $teacher->name;
            $series[] = $teacher->groups->count();
        }

        return [$labels, $series];
    }
}
