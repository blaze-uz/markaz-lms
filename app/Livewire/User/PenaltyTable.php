<?php

namespace App\Livewire\User;

use App\Models\StaffPenalty;
use Illuminate\Support\Carbon;
use Livewire\Component;

class PenaltyTable extends Component
{
    public $user;
    public $date;

    public function mount($user)
    {
        $this->user = $user;
        $this->date = Carbon::now()->subDays(29)->format('Y.m.d') . " - " . Carbon::now()->format('Y.m.d');
    }

    public function render()
    {
        $dates = explode(" - ", $this->date);
        $startDate = Carbon::parse(str_replace('.', '-', $dates[0]))->format('Y-m-d');
        $endDate = Carbon::parse(str_replace('.', '-', $dates[1] ?? $dates[0]))->format('Y-m-d');
        $penalties = StaffPenalty::where('user_id', $this->user->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->latest()
            ->get();
        return view('livewire.user.penalty-table', [
            'penalties' => $penalties,
        ]);
    }
}
