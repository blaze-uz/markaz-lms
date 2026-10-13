<?php

namespace App\Livewire\Group;

use App\Models\Group;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class GroupIndexCards extends Component
{
    public $filterState;

    public function mount(): void
    {
        $this->filterState = request()->get('state');
    }

    public function placeholder()
    {
        return view('livewire.placeholders.skeleton');
    }

    public function render()
    {
        $groupCounts = cache()->rememberForever('groupCounts', function () {
            return $this->getGroupQuery()
                ->selectRaw('state, COUNT(*) as count')
                ->groupBy('state')
                ->pluck('count', 'state');
        });

        $totalGroupsCount = $this->getGroupQuery()->count();

        $statusCounts = [
            'demo' => $groupCounts->get('demo', 0),
            'active' => $groupCounts->get('active', 0),
            'completed' => $groupCounts->get('completed', 0),
            'total' => $totalGroupsCount,
        ];

        return view('livewire.group.group-index-cards', compact('statusCounts'));
    }

    private function getGroupQuery()
    {
        return auth()->user()->isTeacher() ?
            auth()->user()->groups()->getQuery() : Group::query();
    }
}
 