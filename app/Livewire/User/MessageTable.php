<?php

namespace App\Livewire\User;

use App\Models\UserMessage;
use Illuminate\Support\Carbon;
use Livewire\Component;

class MessageTable extends Component
{
    public $user;
    public $date;

    public function mount($user)
    {
        $this->user = $user;
        $this->date = Carbon::now()->subDays(29)->format('Y.m.d') . ' - ' . Carbon::now()->format('Y.m.d');
    }

    public function render()
    {
        $dates = explode(' - ', $this->date);
        $startDate = Carbon::parse(str_replace('.', '-', $dates[0]))->startOfDay();
        $endDate = Carbon::parse(str_replace('.', '-', $dates[1] ?? $dates[0]))->endOfDay();

        $messages = UserMessage::where('user_id', $this->user->id)
            ->with(['createdBy' => function ($query) {
                $query->select('id', 'name');
            }])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->latest()
            ->get();

        return view('livewire.user.message-table', [
            'messages' => $messages,
        ]);
    }
}
