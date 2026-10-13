<?php

namespace App\Livewire\Report\Leads;

use App\Models\LeadCollection;
use Carbon\Carbon;
use Livewire\Component;

class IncomingStatistics extends Component
{
    public $start;

    public $end;

    public function mount($startDate = null, $endDate = null): void
    {
        $this->start = $startDate;
        $this->end = $endDate;
    }

    public function render()
    {
        [$startAt, $endAt] = $this->resolveDateRange();

        $boards_data = [];

        $collection = LeadCollection::query()
            ->orderBy('order')
            ->with(['boards' => function ($query) use ($startAt, $endAt) {
                $query->orderBy('name')
                    ->withCount([
                        'leads as new_tasks_count' => function ($q) use ($startAt, $endAt) {
                            $q->where('status', 'new')
                                ->whereBetween('created_at', [$startAt, $endAt]);
                        },
                    ]);
            }])
            ->first();

        if ($collection) {
            foreach ($collection->boards as $board) {
                if ($board->new_tasks_count > 0) {
                    $boards_data[] = [
                        'x' => $board->name,
                        'y' => $board->new_tasks_count,
                    ];
                }
            }
        }

        return view('livewire.report.leads.incoming-statistics', compact('boards_data'));
    }

    /**
     * @return array{0: \Carbon\Carbon, 1: \Carbon\Carbon}
     */
    private function resolveDateRange(): array
    {
        $start = filled($this->start) ? Carbon::parse($this->start)->startOfDay() : now()->subDays(30)->startOfDay();
        $end = filled($this->end) ? Carbon::parse($this->end)->endOfDay() : now()->endOfDay();

        return [$start, $end];
    }
}
