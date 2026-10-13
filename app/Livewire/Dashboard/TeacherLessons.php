<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;

class TeacherLessons extends Component
{
    public function render()
    {
        $teacher = auth()->user();
        $today = strtolower(now()->format('l'));

        // Optimized query to fetch today's lessons with necessary relationships
        $todayLessons = $teacher->groups()
            ->where('state', 'active')
            ->with(['groupTimes' => function ($query) use ($today) {
                $query->where('name', $today)->orderBy('start');
            }])
            ->get()
            ->pluck('groupTimes')
            ->flatten()
            ->sortBy('start');

        return view('livewire.dashboard.teacher-lessons', compact('todayLessons'));
    }
}
