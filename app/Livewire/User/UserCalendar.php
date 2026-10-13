<?php

namespace App\Livewire\User;

use Livewire\Component;

class UserCalendar extends Component
{
    public $user;

    public function mount($user): void
    {
        $this->user = $user;
    }

    public function render()
    {
        return view('livewire.user.user-calendar');
    }
}
