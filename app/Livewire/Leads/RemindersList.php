<?php

namespace App\Livewire\Leads;

use App\Models\LeadReminder;
use Illuminate\Support\Carbon;
use Livewire\Component;

class RemindersList extends Component
{
    public function render()
    {

        $now = Carbon::now();
        $threeDaysFromNow = $now->copy()->addDays(3);

        $reminders = LeadReminder::whereBetween('reminder_date', [$now, $threeDaysFromNow])
            ->where('reminder_status', '!=', 'completed')
            ->orderBy('reminder_date', 'asc')
            ->get()
            ->groupBy('reminder_date');
        return view('livewire.leads.reminders-list', compact('reminders'));
    }
}
