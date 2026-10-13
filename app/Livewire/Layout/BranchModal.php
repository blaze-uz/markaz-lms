<?php

namespace App\Livewire\Layout;

use App\Models\Branch;
use Livewire\Component;

class BranchModal extends Component
{
    public function render()
    {
        return view('livewire.layout.branch-modal', [
            'branches' => auth()->user()->branches()->get(),
            'currentBranch' => Branch::find(auth()->user()->current_branch_id)
        ]);
    }
}
