<?php

namespace App\Livewire\Report\Leads;

use App\Models\LeadCollection;
use Carbon\Carbon;
use Livewire\Component;

class Funnel extends Component
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

        $collections = LeadCollection::query()
            ->orderBy('order')
            ->withCount([
                'leads as new_tasks_count' => function ($query) use ($startAt, $endAt) {
                    $query->where('status', 'new')
                        ->whereBetween('created_at', [$startAt, $endAt]);
                },
            ])
            ->get();

        $collections_data = [];
        foreach ($collections as $collection) {
            $collections_data[] = [
                'x' => $collection->name,
                'y' => $collection->new_tasks_count,
            ];
        }

        return view('livewire.report.leads.funnel', compact('collections', 'collections_data'));
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
