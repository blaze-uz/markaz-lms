<?php

namespace App\Livewire\Sms;

use App\Models\Sms;
use Illuminate\Support\Carbon;
use Livewire\Component;
use Livewire\WithPagination;

class ScheduledTable extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public string $date;
    public $perPage = 10;
    public $selectedUsers = [];

    public function mount()
    {
        $this->date = Carbon::now()->format('Y.m.d') . " - " . Carbon::now()->format('Y.m.d');
    }

    public function selectAll(): void
    {
        [$startDate, $endDate] = $this->parseDateRange($this->date);

        $allSmsIds = Sms::with('smsable')
            ->where('status', 'scheduled')
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->orderBy('scheduled_at')
            ->limit($this->perPage)
            ->pluck('id')
            ->toArray();

        if (count($this->selectedUsers) === count($allSmsIds)) {
            $this->selectedUsers = [];
        } else {
            $this->selectedUsers = $allSmsIds;
        }
    }


    public function render()
    {
        [$startDate, $endDate] = $this->getDateRange($this->date);

        $sms = Sms::with('smsable')
            ->where('status', 'scheduled')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('scheduled_at')
            ->paginate($this->perPage);

        return view('livewire.sms.scheduled-table', [
            'sms' => $sms,
        ]);
    }

    private function getDateRange()
    {
        $dates = explode(" - ", $this->date);
        $startDate = Carbon::parse(str_replace('.', '-', $dates[0]))->startOfDay();
        $endDate = Carbon::parse(str_replace('.', '-', $dates[1] ?? $dates[0]))->endOfDay();

        return [$startDate, $endDate];
    }

    private function parseDateRange(string $date): array
    {
        $dates = explode(" - ", $date);
        $startDate = Carbon::parse(str_replace('.', '-', $dates[0]))->startOfDay();
        $endDate = Carbon::parse(str_replace('.', '-', $dates[1] ?? $dates[0]))->endOfDay();

        return [$startDate, $endDate];
    }
}
