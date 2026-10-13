<?php

namespace App\Livewire\Report\Leads;

use App\Models\LeadCollection;
use Carbon\Carbon;
use Livewire\Component;

class Cards extends Component
{
    public $startDate;

    public $endDate;

    public function placeholder()
    {
        return view('livewire.placeholders.skeleton');
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

        return view('livewire.report.leads.cards', compact('collections'));
    }

    /**
     * @return array{0: \Carbon\Carbon, 1: \Carbon\Carbon}
     */
    private function resolveDateRange(): array
    {
        $start = filled($this->startDate) ? Carbon::parse($this->startDate)->startOfDay() : now()->subDays(30)->startOfDay();
        $end = filled($this->endDate) ? Carbon::parse($this->endDate)->endOfDay() : now()->endOfDay();

        return [$start, $end];
    }
}
