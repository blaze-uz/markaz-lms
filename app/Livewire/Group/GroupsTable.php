<?php

namespace App\Livewire\Group;

use App\Models\Course;
use App\Models\Group;
use App\Models\User;
use App\Exports\GroupsExport;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class GroupsTable extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    #[Url(as: 'q')]
    public $search = '';
    #[Url(as: 'state')]
    public $filterState = null;
    #[Url(as: 'course')]
    public $filterCourse = null;
    #[Url(as: 'teacher')]
    public $filterTeacher = null;
    public $showFilters = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'filterState' => ['except' => null, 'as' => 'state'],
        'filterCourse' => ['except' => null, 'as' => 'course'],
        'filterTeacher' => ['except' => null, 'as' => 'teacher']
    ];

    public $courses;
    public $teachers;

    public function boot()
    {
        if (!auth()->user()->isTeacher()) {
            $this->courses = Course::select('id', 'name')->orderBy('name')->get();
            $this->teachers = User::role('Teacher')
                ->whereHas('branches', function ($query) {
                    $query->where('branch_id', auth()->user()->current_branch_id);
                })
                ->select('id', 'name')
                ->orderBy('name')
                ->get();
        }
    }

    public function placeholder()
    {
        return <<<'blade'
            <div class="card">
                <div class="card-body">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        blade;
    }

    private function getGroupQuery()
    {
        $query = Group::query()
            ->join('branch_group', 'groups.id', '=', 'branch_group.group_id')
            ->join('branches', function ($join) {
                $join->on('branch_group.branch_id', '=', 'branches.id')
                    ->where('branches.id', auth()->user()->current_branch_id)
                    ->whereNull('branches.deleted_at');
            })
            ->whereNull('groups.deleted_at');

        if (auth()->user()->isTeacher()) {
            $query->where('groups.teacher_id', auth()->id());
        }

        return $query;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function setFilterState($state)
    {
        $previousState = $this->filterState;
        $this->filterState = $state === 'null' ? null : $state;

        $this->resetPage();

        // Dispatch event for other components to listen
        $this->dispatch('filterCardClicked', [
            'previousState' => $previousState,
            'newState' => $this->filterState,
            'timestamp' => now()->toISOString(),
            'user' => auth()->user()->name ?? 'Unknown'
        ]);
    }

    public function updatedFilterCourse()
    {
        $this->resetPage();
    }

    public function updatedFilterTeacher()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->filterState = null;
        if (!auth()->user()->isTeacher()) {
            $this->filterCourse = null;
            $this->filterTeacher = null;
        }
        $this->search = '';
        $this->resetPage();
        $this->dispatch('filtersReset');
    }

    public function toggleFilters()
    {
        $this->showFilters = !$this->showFilters;
    }

    public function render()
    {
        $query = $this->getGroupQuery();

        if (!auth()->user()->isTeacher()) {
            if ($this->filterCourse) {
                $query->where('groups.course_id', $this->filterCourse);
            }

            if ($this->filterTeacher) {
                $query->where('groups.teacher_id', $this->filterTeacher);
            }
        }

        // Get counts for status cards
        $statusQuery = (clone $query);
        $totalCount = $statusQuery->count();

        $statusCounts = [
            'total' => $totalCount,
            'active' => (clone $statusQuery)->where('groups.state', 'active')->count(),
            'demo' => (clone $statusQuery)->where('groups.state', 'demo')->count(),
            'completed' => (clone $statusQuery)->where('groups.state', 'completed')->count(),
        ];

        if ($this->search) {
            $query->where(function ($query) {
                $query->where('groups.name', 'ilike', "%{$this->search}%")
                    ->when(!auth()->user()->isTeacher(), function ($q) {
                        $q->orWhereHas('course', function ($q) {
                            $q->where('name', 'ilike', "%{$this->search}%");
                        })
                            ->orWhereHas('teacher', function ($q) {
                                $q->where('name', 'ilike', "%{$this->search}%");
                            });
                    });
            });
        }

        if ($this->filterState) {
            $query->where('groups.state', $this->filterState);
        }

        $items = $query->with([
            'course:id,name',
            'teacher:id,name',
        ])
            ->select([
                'groups.id',
                'groups.name',
                'groups.course_id',
                'groups.teacher_id',
                'groups.state',
                'groups.price'
            ])
            ->withCount('students')
            ->latest('groups.id')
            ->paginate(10);

        return view('livewire.group.groups-table', [
            'courses' => $this->courses,
            'teachers' => $this->teachers,
            'items' => $items,
            'statusCounts' => $statusCounts
        ]);
    }

    /**
     * Export groups data to Excel or CSV
     *
     * @param string $type The export type (excel or csv)
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportGroups($type)
    {
        $filters = [
            'search' => $this->search,
            'state' => $this->filterState,
            'course' => $this->filterCourse,
            'teacher' => $this->filterTeacher,
        ];

        if ($type === 'csv') {
            return Excel::download(new GroupsExport($filters), 'groups.csv');
        }

        return Excel::download(new GroupsExport($filters), 'groups.xlsx');
    }
}
