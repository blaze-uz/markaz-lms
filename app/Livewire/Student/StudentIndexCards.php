<?php

namespace App\Livewire\Student;

use App\Models\Group;
use App\Models\Student;
use App\Services\StudentCacheService;
use Livewire\Component;

class StudentIndexCards extends Component
{
    public $filters = [];

    public $group_ids = [];

    public function mount()
    {
        $this->filters = [
            'status' => request()->query('status', ''),
            'group_id' => request()->query('group_id', ''),
        ];

        if (auth()->user()->role('Teacher')) {
            $this->group_ids = Group::where('teacher_id', auth()->id())->pluck('id')->toArray();
        }
    }

    public function render()
    {
        $counts = $this->getCounts();

        return view('livewire.student.student-index-cards', compact('counts'));
    }

    public function getCounts(): array
    {
        $cacheKey = StudentCacheService::getIndexCardsCacheKey(auth()->user()->current_branch_id);
        StudentCacheService::trackCacheKey($cacheKey);

        $data = cache()->rememberForever($cacheKey, function () {
            return [
                'studentsCount' => $this->getStudentsCount(),
                'unattachedStudentsCount' => $this->getUnattachedStudentsCount(),
                'deletedStudentsCount' => $this->getDeletedStudentsCount(),
                'debtStudentsCount' => $this->getDebtStudentsCount(),
            ];
        });

        return [
            'studentsCount' => $data['studentsCount'],
            'unattachedStudentsCount' => $data['unattachedStudentsCount'],
            'deletedStudentsCount' => $data['deletedStudentsCount'],
            'debtStudentsCount' => $data['debtStudentsCount'],
        ];
    }

    public function getStudentsCount()
    {
        if (auth()->user()->hasRole('Teacher')) {
            return Student::whereHas('groups', function ($query) {
                $query->whereIn('group_id', $this->group_ids);
            })->count();
        }

        return Student::count();
    }

    public function getDeletedStudentsCount()
    {
        if (auth()->user()->hasRole('Teacher')) {
            return 0;
        }

        return Student::onlyTrashed()->count();
    }

    public function getDebtStudentsCount()
    {
        if (auth()->user()->hasRole('Teacher')) {
            return Student::whereRelation('groupPayments', 'left_amount', '>', 0)
                ->whereHas('groups', function ($query) {
                    $query->whereIn('group_id', $this->group_ids);
                })->count();
        }

        return Student::whereRelation('groupPayments', 'left_amount', '>', 0)->count();
    }

    public function getUnattachedStudentsCount()
    {
        if (auth()->user()->hasRole('Teacher')) {
            return 0;
        }

        return Student::whereDoesntHave('groups')->count();
    }
}
