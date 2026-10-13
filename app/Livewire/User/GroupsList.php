<?php

namespace App\Livewire\User;

use App\Models\Group;
use Livewire\Component;

class GroupsList extends Component
{
    public $user;
    public $search = '';
    public $state = 'active';

    public function mount($user)
    {
        $this->user = $user;
    }

    public function render()
    {
        $groups_count = Group::where('teacher_id', $this->user->id)->count();

        $groups = Group::search($this->search)
            ->when($this->state !== 'all', function ($query) {
                return $query->where('state', $this->state);
            })
            ->where('teacher_id', $this->user->id)
            ->withCount('students')
            ->orderBy('name')
            ->get();

        return view('livewire.user.groups-list', [
            "groups" => $groups,
            "groups_count" => $groups_count
        ]);
    }
}
