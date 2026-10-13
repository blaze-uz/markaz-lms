<?php

namespace App\Livewire\Dashboard;

use App\Models\Branch;
use App\Models\Group;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class DashboardCards extends Component
{
    public $currentBranch;
    public $studentsCount;
    public $groupsCount;
    public $teachersCount;
    public $coursesCount;

    public function mount()
    {
        $this->currentBranch = Branch::find(auth()->user()->current_branch_id);
        $this->getData();
    }

    public function render()
    {
        return view('livewire.dashboard.dashboard-cards');
    }

    private function getData()
    {
        $branchId = auth()->user()->current_branch_id;
        $cacheKey = "students_count_{$branchId}";

        $this->studentsCount = Cache::remember($cacheKey, now()->addMinutes(30), function () {
            return $this->getStudentsCount();
        });

        $this->groupsCount = Cache::remember("groups_count_{$branchId}", now()->addMinutes(30), function () {
            return Group::count();
        });

        $this->teachersCount = User::whereHas('branches', function ($query) {
            $query->where('branch_id', $this->currentBranch->id);
        })->count();

        $this->coursesCount = Cache::remember("courses_count_{$branchId}", now()->addMinutes(30), function () {
            return DB::table('courses')->count();
        });
    }

    private function getStudentsCount()
    {
        $currentBranchId = $this->currentBranch->id;

        if ($currentBranchId) {
            return Student::whereHas('branches', function ($query) use ($currentBranchId) {
                $query->where('branch_id', $currentBranchId);
            })->count();
        }

        return Student::count();
    }
}
