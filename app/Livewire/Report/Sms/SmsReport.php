<?php

namespace App\Livewire\Report\Sms;

use App\Traits\HasDateRange;
use Livewire\Component;

class SmsReport extends Component
{
    use HasDateRange;

    public function mount()
    {
        if (request()->from && request()->to) {
            $this->date = request()->from . ' - ' . request()->to;
            $this->startDate = request()->from;
            $this->endDate = request()->to;
        } else {
            // Set default to current month
            $this->startDate = now()->startOfMonth()->format('Y-m-d');
            $this->endDate = now()->endOfMonth()->format('Y-m-d');
            $this->date = $this->startDate . ' - ' . $this->endDate;
        }
    }

    public function render()
    {
        return view('livewire.report.sms.sms-report');
    }
}
