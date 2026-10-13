<?php

namespace App\Livewire\Report\Leads;

use App\Models\LeadCall;
use Carbon\Carbon;
use Livewire\Component;

class CallsStatistics extends Component
{
    public $from;

    public $to;

    public function mount($startDate = null, $endDate = null): void
    {
        $this->from = $startDate;
        $this->to = $endDate;
    }

    public function render()
    {
        [$labels, $series] = $this->getData();

        return view('livewire.report.leads.calls-statistics', [
            'labels' => $labels,
            'series' => $series,
        ]);
    }

    /**
     * @return array{0: array<int, string>, 1: array<int, int>}
     */
    private function getData(): array
    {
        $from = filled($this->from) ? Carbon::parse($this->from)->toDateString() : now()->startOfMonth()->toDateString();
        $to = filled($this->to) ? Carbon::parse($this->to)->toDateString() : now()->endOfMonth()->toDateString();

        $counts = LeadCall::query()
            ->selectRaw('status, COUNT(*) as total')
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->groupBy('status')
            ->pluck('total', 'status');

        $statuses = [
            'answered' => __('translation.leads.answered'),
            'not_answered' => __('translation.leads.not_answered'),
            'busy' => __('translation.leads.busy'),
            'declined' => __('translation.leads.declined'),
        ];

        $labels = array_values($statuses);
        $series = array_map(
            fn (string $status): int => (int) ($counts[$status] ?? 0),
            array_keys($statuses)
        );

        return [$labels, $series];
    }
}
