<?php

namespace App\Livewire\Dashboard;

use App\Models\Student;
use Livewire\Component;

class BirthdayCard extends Component
{
    public function render()
    {
        //set cache key
        $send_birthdays_sms = cache('send_birthdays_sms' . now()->format('Y-m-d'));

        $students_count = Student::whereNotNull('birthday')
            ->whereMonth('birthday', now()->month)
            ->whereDay('birthday', now()->day)
            ->count();

        $show = $students_count > 0 && !$send_birthdays_sms;

        return view('livewire.dashboard.birthday-card', [
            'students_count' => $students_count,
            'show' => $show,
        ]);
    }
}
