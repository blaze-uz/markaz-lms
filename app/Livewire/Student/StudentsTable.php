<?php

namespace App\Livewire\Student;

use App\Exports\StudentsExport;
use App\Models\Group;
use App\Models\Student;
use App\Models\User;
use App\Services\StudentCacheService;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class StudentsTable extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = ['refreshComponent' => '$refresh'];

    #[Url(as: 'page')]
    public $page = 1;

    public $filters;

    public $showFilters = false;

    #[Url(as: 'q')]
    public $search = '';

    public $selectedStudents = [];

    public $selectAllAcrossPages = false;

    public $showSelectAllBanner = false;

    public $selectAllCurrentPage = false;

    #[Url(as: 'state')]
    public $filterState = 'studentsCount';

    #[Url(as: 'perPage')]
    public $perPage = 10;

    #[Url(as: 'group_id')]
    public $group_id = null;

    #[Url(as: 'teacher_id')]
    public $teacherId = null;

    #[Url(as: 'from')]
    public $dateFrom = '';

    #[Url(as: 'to')]
    public $dateTo = '';

    #[Url(as: 'show_all_branches')]
    public $showAllBranches = false;

    // Delete functionality properties
    public $showDeleteModal = false;

    public $deleteComment = '';

    public $studentsToDelete = [];

    public $isDeleting = false;

    public $showRestoreModal = false;

    public $studentsToRestore = [];

    public $isRestoring = false;

    public $showForceDeleteModal = false;

    public $studentsToForceDelete = [];

    public $isForceDeleting = false;

    public function setFilterState($state)
    {
        $this->filterState = $state === 'null' ? null : $state;
        $this->deselectAllStudents();
        $this->resetPage();
    }

    public function updatedFilterState(): void
    {
        $this->deselectAllStudents();
        $this->resetPage();
    }

    protected function queryString(): array
    {
        return [
            'search' => [
                'as' => 'q',
            ],
            'page' => ['except' => 1],
            'showAllBranches' => ['as' => 'show_all_branches', 'except' => false],
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'group_id', 'teacherId', 'filterState', 'dateFrom', 'dateTo', 'showAllBranches']);
        $this->perPage = 10;
        $this->showFilters = false;
        $this->resetPage();
    }

    public function mount()
    {
        $this->filters = [
            'search' => $this->search,
            'group_id' => $this->group_id,
            'teacher_id' => $this->teacherId,
            'perPage' => $this->perPage,
            'state' => $this->filterState,
            'dateFrom' => $this->dateFrom,
            'dateTo' => $this->dateTo,
            'showAllBranches' => $this->showAllBranches,
        ];

        $this->search = $this->filters['search'];
    }

    public function updatedSelectAllCurrentPage($value): void
    {
        if ($value) {
            // Select all on current page
            $allStudentIds = $this->getStudents()->pluck('id')->toArray();
            $this->selectedStudents = $allStudentIds;

            // Show banner only if there are more pages
            $students = $this->getStudents();
            $totalStudents = $students->total();
            $currentPageCount = count($allStudentIds);

            if ($currentPageCount < $totalStudents) {
                $this->showSelectAllBanner = true;
            }
        } else {
            // Deselect all
            $this->selectedStudents = [];
            $this->showSelectAllBanner = false;
            $this->selectAllAcrossPages = false;
        }
    }

    public function updatedSelectedStudents(): void
    {
        // If user manually deselects items, reset the "across pages" flag
        if ($this->selectAllAcrossPages) {
            $totalCount = $this->buildStudentsQuery()->count();
            if (count($this->selectedStudents) < $totalCount) {
                $this->selectAllAcrossPages = false;
            }
        }

        // Auto-check the select all checkbox if all items on current page are selected
        $currentPageIds = $this->getStudents()->pluck('id')->toArray();
        $allCurrentPageSelected = count($currentPageIds) > 0 &&
            count(array_intersect($currentPageIds, $this->selectedStudents)) === count($currentPageIds);

        $this->selectAllCurrentPage = $allCurrentPageSelected;
    }

    public function selectAllStudentsAcrossPages(): void
    {
        // Get all student IDs matching current filters (without pagination)
        $query = $this->buildStudentsQuery();
        $this->selectedStudents = $query->pluck('students.id')->toArray();
        $this->selectAllAcrossPages = true;
        $this->showSelectAllBanner = false;
    }

    public function deselectAllStudents(): void
    {
        $this->selectedStudents = [];
        $this->selectAllAcrossPages = false;
        $this->showSelectAllBanner = false;
        $this->selectAllCurrentPage = false;
    }

    private function shouldBypassCountsCache(): bool
    {
        return filled($this->search)
            || filled($this->dateFrom)
            || filled($this->dateTo)
            || filled($this->group_id)
            || filled($this->teacherId)
            || $this->showAllBranches;
    }

    private function buildStudentsQuery(?string $filterStateOverride = null)
    {
        $filterState = $filterStateOverride ?? $this->filterState;

        $user = auth()->user();
        $isTeacher = $user->hasRole('Teacher');
        $teacherId = $user->id;

        $query = Student::query()
            ->select([
                'students.id',
                'students.name',
                'students.surname',
                'students.phone',
                'students.balance',
                'students.account_id',
                'students.created_by_id',
                'students.created_at',
                'students.deleted_at',
            ]);

        // Apply teacher filter
        if ($isTeacher) {
            $groupIds = cache()->remember('teacher_groups_'.$teacherId, now()->addMinutes(30), function () {
                return auth()->user()->groups()->pluck('id')->toArray();
            });

            $query->where(function ($query) use ($groupIds, $teacherId) {
                $query->whereHas('groups', function ($teacherQuery) use ($groupIds) {
                    return $teacherQuery->whereIn('group_id', $groupIds);
                })->orWhere('created_by_id', $teacherId);
            });
        }
        // Apply branch filter only if not showing all branches and not a teacher
        elseif (! $this->showAllBranches) {
            $currentBranchId = $user->current_branch_id;

            if ($currentBranchId) {
                $query->whereHas('branches', function ($branchQuery) use ($currentBranchId) {
                    $branchQuery->where('branch_id', $currentBranchId);
                });
            }
        } elseif ($this->showAllBranches) {
            $query->withoutGlobalScope('branch');
        }

        // Apply status filters
        if ($filterState === 'activeStudentsCount') {
            $query->whereNull('deleted_at')->has('groups');
        } elseif ($filterState === 'debtStudentsCount') {
            $query->whereHas('groupPayments', function ($q) {
                $q->where('left_amount', '>', 0);
            });
        } elseif ($filterState === 'unattachedStudentsCount') {
            $query->whereDoesntHave('groups');
        } elseif ($filterState === 'deletedStudentsCount') {
            $query->onlyTrashed();

            // Add date range filter for deleted students
            if ($this->dateFrom) {
                $query->where('deleted_at', '>=', $this->dateFrom);
            }
            if ($this->dateTo) {
                $query->where('deleted_at', '<=', $this->dateTo);
            }
        }

        // Apply search filter
        if ($this->search) {
            $searchTerm = '%'.$this->search.'%';
            $query->where(function ($query) use ($searchTerm) {
                $query->where('phone', 'ilike', $searchTerm)
                    ->orWhereRaw("CONCAT(name, ' ', surname) ILIKE ?", [$searchTerm])
                    ->orWhereRaw("CONCAT(surname, ' ', name) ILIKE ?", [$searchTerm])
                    ->orWhere('name', 'ilike', $searchTerm)
                    ->orWhere('surname', 'ilike', $searchTerm);
            });
        }

        // Apply group filter
        if ($this->group_id) {
            $query->whereHas('groups', function ($q) {
                $q->where('group_id', $this->group_id);
            });
        }

        if (! $isTeacher && $this->teacherId) {
            $query->whereHas('groups', function ($q) {
                $q->where('teacher_id', $this->teacherId);
            });
        }

        // Apply date filters
        if ($this->dateFrom && $filterState !== 'deletedStudentsCount') {
            $query->where('created_at', '>=', $this->dateFrom);
        }

        if ($this->dateTo && $filterState !== 'deletedStudentsCount') {
            $query->where('created_at', '<=', $this->dateTo);
        }

        return $query;
    }

    public function redirectToSendSms()
    {
        $sendSmsIds = json_encode($this->selectedStudents);

        return redirect()->route('admin.sms.create', ['sendSmsIds' => $sendSmsIds]);
    }

    public function redirectToSendTelegram()
    {
        $sendTelegramIds = json_encode($this->selectedStudents);

        return redirect()->route('admin.telegram.send', ['sendTelegramIds' => $sendTelegramIds]);
    }

    public function redirectToAttachGroup()
    {
        return redirect()->route('admin.groups.attach-student.view', ['students_ids' => $this->selectedStudents]);
    }

    public function openDeleteModal()
    {
        if (empty($this->selectedStudents)) {
            session()->flash('error_alert', __('translation.students.select_students_to_delete'));

            return;
        }

        $this->studentsToDelete = $this->selectedStudents;
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->deleteComment = '';
        $this->studentsToDelete = [];
        $this->isDeleting = false;
    }

    public function deleteSelectedStudents()
    {
        abort_unless(auth()->user()?->can('student-delete'), 403);

        // Prevent double execution
        if ($this->isDeleting) {
            return;
        }

        $this->validate([
            'deleteComment' => 'required|min:3',
        ], [
            'deleteComment.required' => __('translation.students.delete_comment_required'),
            'deleteComment.min' => __('translation.students.delete_comment_min'),
        ]);

        $this->isDeleting = true;

        try {
            $deletedCount = 0;
            foreach ($this->studentsToDelete as $studentId) {
                $student = Student::find($studentId);
                if ($student && ! $student->trashed()) {
                    // Use the same service method as the controller
                    app(\App\Services\StudentService::class)->destroy($studentId, $this->deleteComment);
                    $deletedCount++;
                }
            }

            $this->closeDeleteModal();
            $this->selectedStudents = [];

            session()->flash('success_alert', __('translation.students.deleted_successfully', ['count' => $deletedCount]));

            // Refresh the component
            $this->dispatch('refreshComponent');
        } catch (\Exception $e) {
            session()->flash('error_alert', $e->getMessage());
        } finally {
            $this->isDeleting = false;
        }
    }

    public function openRestoreModal(): void
    {
        if (empty($this->selectedStudents)) {
            session()->flash('error_alert', __('translation.students.select_students_to_restore'));

            return;
        }

        $this->studentsToRestore = $this->selectedStudents;
        $this->showRestoreModal = true;
    }

    public function closeRestoreModal(): void
    {
        $this->showRestoreModal = false;
        $this->studentsToRestore = [];
        $this->isRestoring = false;
    }

    public function restoreSelectedStudents(): void
    {
        abort_unless(auth()->user()?->can('student-update'), 403);

        if ($this->isRestoring) {
            return;
        }

        $this->isRestoring = true;

        try {
            $studentService = app(\App\Services\StudentService::class);
            $restoredCount = 0;

            foreach ($this->studentsToRestore as $studentId) {
                $studentService->checkPermission($studentId);
                $student = Student::onlyTrashed()->find($studentId);

                if ($student) {
                    $studentService->restore($studentId);
                    $restoredCount++;
                }
            }

            $this->closeRestoreModal();
            $this->selectedStudents = [];

            session()->flash('success_alert', __('translation.students.restored_successfully', ['count' => $restoredCount]));
            $this->dispatch('refreshComponent');
        } catch (\Exception $e) {
            session()->flash('error_alert', $e->getMessage());
        } finally {
            $this->isRestoring = false;
        }
    }

    public function openForceDeleteModal(): void
    {
        if (empty($this->selectedStudents)) {
            session()->flash('error_alert', __('translation.students.select_students_to_delete'));

            return;
        }

        $this->studentsToForceDelete = $this->selectedStudents;
        $this->showForceDeleteModal = true;
    }

    public function closeForceDeleteModal(): void
    {
        $this->showForceDeleteModal = false;
        $this->studentsToForceDelete = [];
        $this->isForceDeleting = false;
    }

    public function forceDeleteSelectedStudents(): void
    {
        abort_unless(auth()->user()?->can('student-delete'), 403);

        if ($this->isForceDeleting) {
            return;
        }

        $this->isForceDeleting = true;

        try {
            $studentService = app(\App\Services\StudentService::class);
            $deletedCount = 0;

            foreach ($this->studentsToForceDelete as $studentId) {
                $studentService->checkPermission($studentId);
                $student = Student::onlyTrashed()->find($studentId);

                if ($student) {
                    $studentService->forceDelete($studentId);
                    $deletedCount++;
                }
            }

            $this->closeForceDeleteModal();
            $this->selectedStudents = [];

            session()->flash('success_alert', __('translation.students.force_deleted_successfully', ['count' => $deletedCount]));
            $this->dispatch('refreshComponent');
        } catch (\Exception $e) {
            session()->flash('error_alert', $e->getMessage());
        } finally {
            $this->isForceDeleting = false;
        }
    }

    public function render()
    {
        $statusCounts = $this->getCounts();
        $items = $this->getStudents();
        $groups = $this->getGroups();
        $teachers = $this->getTeachers();

        return view('livewire.student.students-table', [
            'items' => $items,
            'groups' => $groups,
            'teachers' => $teachers,
            'statusCounts' => $statusCounts,
        ]);
    }

    public function getCounts(): array
    {
        $branchId = auth()->user()->current_branch_id;
        $cacheKey = StudentCacheService::getStatsCacheKey($branchId);

        StudentCacheService::trackCacheKey($cacheKey);

        if ($this->shouldBypassCountsCache()) {
            $data = [
                'studentsCount' => $this->buildStudentsQuery('studentsCount')->count(),
                'activeStudentsCount' => $this->buildStudentsQuery('activeStudentsCount')->count(),
                'unattachedStudentsCount' => $this->buildStudentsQuery('unattachedStudentsCount')->count(),
                'debtStudentsCount' => $this->buildStudentsQuery('debtStudentsCount')->count(),
                'deletedStudentsCount' => $this->buildStudentsQuery('deletedStudentsCount')->count(),
            ];
        } else {
            $data = cache()->remember($cacheKey, now()->addMinutes(30), function () {
                return [
                    'studentsCount' => $this->getStudentsCount(),
                    'activeStudentsCount' => $this->getActiveStudentsCount(),
                    'unattachedStudentsCount' => $this->getUnattachedStudentsCount(),
                    'debtStudentsCount' => $this->getDebtStudentsCount(),
                    'deletedStudentsCount' => $this->getDeletedStudentsCount(),
                ];
            });
        }

        return [
            'studentsCount' => $data['studentsCount'],
            'activeStudentsCount' => $data['activeStudentsCount'],
            'unattachedStudentsCount' => $data['unattachedStudentsCount'],
            'debtStudentsCount' => $data['debtStudentsCount'],
            'deletedStudentsCount' => $data['deletedStudentsCount'],
        ];
    }

    public function getStudentsCount()
    {
        if (auth()->user()->hasRole('Teacher')) {
            return Student::whereHas('groups', function ($query) {
                $query->whereIn('group_id', auth()->user()->groups->pluck('id'));
            })->count();
        }

        $currentBranchId = auth()->user()->current_branch_id;
        if ($currentBranchId) {
            return Student::whereHas('branches', function ($query) use ($currentBranchId) {
                $query->where('branch_id', $currentBranchId);
            })->count();
        }

        return Student::count();
    }

    public function getActiveStudentsCount()
    {
        if (auth()->user()->hasRole('Teacher')) {
            return Student::whereHas('groups', function ($query) {
                $query->whereIn('group_id', auth()->user()->groups->pluck('id'));
            })->whereNull('deleted_at')->count();
        }

        $currentBranchId = auth()->user()->current_branch_id;
        if ($currentBranchId) {
            return Student::has('groups')
                ->whereNull('deleted_at')
                ->whereHas('branches', function ($query) use ($currentBranchId) {
                    $query->where('branch_id', $currentBranchId);
                })->count();
        }

        return Student::has('groups')->whereNull('deleted_at')->count();
    }

    public function getDebtStudentsCount()
    {
        if (auth()->user()->hasRole('Teacher')) {
            return Student::whereRelation('groupPayments', 'left_amount', '>', 0)
                ->whereHas('groups', function ($query) {
                    $query->whereIn('group_id', auth()->user()->groups->pluck('id'));
                })->count();
        }

        $currentBranchId = auth()->user()->current_branch_id;
        if ($currentBranchId) {
            return Student::whereRelation('groupPayments', 'left_amount', '>', 0)
                ->whereHas('branches', function ($query) use ($currentBranchId) {
                    $query->where('branch_id', $currentBranchId);
                })->count();
        }

        return Student::whereRelation('groupPayments', 'left_amount', '>', 0)->count();
    }

    public function getUnattachedStudentsCount()
    {
        if (auth()->user()->hasRole('Teacher')) {
            return 0;
        }

        $currentBranchId = auth()->user()->current_branch_id;
        if ($currentBranchId) {
            return Student::whereDoesntHave('groups')
                ->whereHas('branches', function ($query) use ($currentBranchId) {
                    $query->where('branch_id', $currentBranchId);
                })->count();
        }

        return Student::whereDoesntHave('groups')->count();
    }

    public function getDeletedStudentsCount()
    {
        if (auth()->user()->hasRole('Teacher')) {
            return Student::onlyTrashed()
                ->whereHas('groups', function ($query) {
                    $query->whereIn('group_id', auth()->user()->groups->pluck('id'));
                })->count();
        }

        $currentBranchId = auth()->user()->current_branch_id;
        if ($currentBranchId) {
            return Student::onlyTrashed()
                ->whereHas('branches', function ($query) use ($currentBranchId) {
                    $query->where('branch_id', $currentBranchId);
                })->count();
        }

        return Student::onlyTrashed()->count();
    }

    private function getStudents()
    {
        try {
            $query = $this->buildStudentsQuery();

            // Add relationships
            $query->with([
                'groups:id,name',
                'created_by:id,name',
                'branches:id,name',
                'groupPayments' => function ($query) {
                    $query->select('id', 'student_id', 'left_amount')
                        ->where('left_amount', '>', 0);
                },
            ]);

            // Order by
            if ($this->filterState === 'deletedStudentsCount') {
                $query->orderBy('deleted_at', 'desc');
            } else {
                $query->orderBy('students.created_at', 'desc');
            }

            return $query->paginate($this->perPage);
        } catch (\Exception $e) {
            logger()->error('Error in getStudents: '.$e->getMessage());

            return collect([]);
        }
    }

    private function getGroups()
    {
        $user = auth()->user();
        $isTeacher = $user->hasRole('Teacher');

        $query = Group::query()
            ->whereNull('groups.deleted_at');

        if ($isTeacher) {
            return $query->select([
                'groups.id',
                'groups.name',
                DB::raw('(SELECT COUNT(*) FROM group_student WHERE group_student.group_id = groups.id) as students_count'),
            ])
                ->where('teacher_id', $user->id)
                ->orderBy('name')
                ->get();
        }

        return $query->select([
            'groups.id',
            'groups.name',
            'groups.teacher_id',
            'users.name as teacher_name',
            DB::raw('(SELECT COUNT(*) FROM group_student WHERE group_student.group_id = groups.id) as students_count'),
        ])
            ->join('users', 'groups.teacher_id', '=', 'users.id')
            ->whereNull('users.deleted_at')
            ->orderBy('groups.name')
            ->get();
    }

    private function getTeachers(): \Illuminate\Support\Collection
    {
        $user = auth()->user();

        if ($user->hasRole('Teacher')) {
            return collect();
        }

        $query = User::role('Teacher')
            ->select(['users.id', 'users.name'])
            ->orderBy('users.name');

        if (! $this->showAllBranches && $user->current_branch_id) {
            $query->whereHas('branches', function ($query) use ($user) {
                $query->where('branch_id', $user->current_branch_id);
            });
        }

        return $query->get();
    }

    public function updatedDateFrom()
    {
        $this->resetPage();
    }

    public function updatedDateTo()
    {
        $this->resetPage();
    }

    public function updatedShowAllBranches()
    {
        $this->resetPage();
    }

    public function updatedTeacherId(): void
    {
        $this->resetPage();
    }

    public function exportStudents($type)
    {
        $filter = [
            'search' => $this->search,
            'group_id' => $this->group_id,
            'teacher_id' => $this->teacherId,
            'state' => $this->filterState,
            'status' => null,
            'dateFrom' => $this->dateFrom,
            'dateTo' => $this->dateTo,
            'showAllBranches' => $this->showAllBranches,
        ];

        if ($type === 'csv') {
            return Excel::download(new StudentsExport($filter), 'students.csv');
        }

        return Excel::download(new StudentsExport($filter), 'students.xlsx');
    }

    public function refreshCounts()
    {
        $branchId = auth()->user()->current_branch_id;
        StudentCacheService::clearCache($branchId);
    }
}
