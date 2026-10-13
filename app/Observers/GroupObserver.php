<?php

namespace App\Observers;

use App\Models\Group;
use Illuminate\Support\Facades\Cache;

class GroupObserver
{
    /**
     * Handle the Group "created" event.
     */
    public function created(Group $group): void
    {
        Cache::forget('groups_count');
    }

    /**
     * Handle the Group "deleted" event.
     */
    public function deleted(Group $group): void
    {
        Cache::forget('groups_count');
    }

    /**
     * Handle the Group "restored" event.
     */
    public function restored(Group $group): void
    {
        Cache::forget('groups_count');
    }

    /**
     * Handle the Group "force deleted" event.
     */
    public function forceDeleted(Group $group): void
    {
        Cache::forget('groups_count');
    }
}
