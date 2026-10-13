<?php

namespace App\Traits;

use App\Models\Branch;

trait HasBranch
{
    public static function bootHasBranch(): void
    {
        self::addGlobalScope('branch', function ($query) {
            if (!auth()->check()) {
                return;
            }

            $user = auth()->user();
            $currentBranchId = $user->current_branch_id;

            if ($currentBranchId) {
                $query->whereHas('branches', function ($branchQuery) use ($currentBranchId) {
                    $branchQuery->where('branch_id', $currentBranchId);
                });
            }
        });
    }

    public function branches()
    {
        return $this->belongsToMany(Branch::class);
    }
}