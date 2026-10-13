<?php

namespace App\Livewire\Student;

use App\Models\Student;
use Livewire\Component;

class PhoneInputValidation extends Component
{
    public $phone;
    public $errorMessage = '';
    public $studentInOtherBranch = null;
    public $branchName = '';

    public function search()
    {
        $phone = preg_replace('/[^0-9]/', '', $this->phone);
        $phone = substr($phone, 3);
        
        // Get current user's branch ID
        $currentBranchId = auth()->user()->current_branch_id;
        
        // Find student in current branch
        $student = Student::where('phone', $phone)
            ->whereHas('branches', function($query) use ($currentBranchId) {
                $query->where('branch_id', $currentBranchId);
            })
            ->first();
            
        // Find student in any branch (without branch scope)
        $studentInAnyBranch = Student::withoutGlobalScope('branch')
            ->where('phone', $phone)
            ->first();
            
        // Find deleted student
        $deletedStudent = Student::where('phone', $phone)->onlyTrashed()->first();
        
        if ($student) {
            $this->errorMessage = __("translation.students.already_exists");
            $this->studentInOtherBranch = null;
        } elseif ($studentInAnyBranch) {
            // Student exists but in a different branch
            $branch = $studentInAnyBranch->branches()->first();
            $this->branchName = $branch ? $branch->name : __('translation.unknown_branch');
            $this->errorMessage = __("translation.students.exists_in_other_branch", ['branch' => $this->branchName]);
            $this->studentInOtherBranch = $studentInAnyBranch;
        } elseif ($deletedStudent) {
            $this->errorMessage = __("translation.students.already_exists_in_trash");
            $this->studentInOtherBranch = null;
        } else {
            $this->errorMessage = '';
            $this->studentInOtherBranch = null;
        }
    }

    public function render()
    {
        return view('livewire.student.phone-input-validation');
    }
}
