<?php

namespace App\Livewire\Report\Leads;

use App\Models\Lead;
use App\Models\LeadCollection;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Component;

class BoardsStatistics extends Component
{
    public $start;

    public $end;

    public function mount($startDate = null, $endDate = null): void
    {
        $this->start = $startDate;
        $this->end = $endDate;
    }

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
                'leads as unsigned_tasks_count' => function ($query) use ($startAt, $endAt) {
                    $query->whereNull('board_id')
                        ->whereBetween('created_at', [$startAt, $endAt]);
                },
            ])
            ->with(['boards' => function ($query) use ($startAt, $endAt) {
                $query->orderBy('name')
                    ->withCount([
                        'leads as new_tasks_count' => function ($q) use ($startAt, $endAt) {
                            $q->where('status', 'new')
                                ->whereBetween('created_at', [$startAt, $endAt]);
                        },
                    ]);
            }])
            ->get();

        $leadsPerCauser = Lead::query()
            ->whereBetween('created_at', [$startAt, $endAt])
            ->whereNotNull('causer_id')
            ->selectRaw('causer_id, COUNT(*) as leads_count')
            ->groupBy('causer_id')
            ->pluck('leads_count', 'causer_id');

        $managers = User::query()
            ->whereIn('id', $leadsPerCauser->keys())
            ->get()
            ->map(function (User $user) use ($leadsPerCauser) {
                $user->setAttribute('tasks_count', (int) ($leadsPerCauser[$user->id] ?? 0));

                return $user;
            })
            ->sortByDesc('tasks_count')
            ->values();

        $managersTotalLeads = (int) $leadsPerCauser->sum();

        return view('livewire.report.leads.boards-statistics', [
            'collections' => $collections,
            'managers' => $managers,
            'managersTotalLeads' => $managersTotalLeads,
        ]);
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
