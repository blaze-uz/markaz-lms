<?php

namespace App\Livewire\Group;

use App\Models\Group;
use App\Models\GroupTime;
use App\Models\Room;
use Illuminate\Support\Carbon;
use Livewire\Component;

class GroupTimeForm extends Component
{
    public $activeTab = 'odd';
    public $group, $rooms;
    public $isOddDay = false;
    public $isEvenDay = false;
    public $isSunday = false;
    public $monday = ['start' => null, 'end' => null, 'name' => 'monday', 'room_id' => null, 'errors' => []],
        $tuesday = ['start' => null, 'end' => null, 'name' => 'tuesday', 'room_id' => null, 'errors' => []],
        $wednesday = ['start' => null, 'end' => null, 'name' => 'wednesday', 'room_id' => null, 'errors' => []],
        $thursday = ['start' => null, 'end' => null, 'name' => 'thursday', 'room_id' => null, 'errors' => []],
        $friday = ['start' => null, 'end' => null, 'name' => 'friday', 'room_id' => null, 'errors' => []],
        $saturday = ['start' => null, 'end' => null, 'name' => 'saturday', 'room_id' => null, 'errors' => []],
        $sunday = ['start' => null, 'end' => null, 'name' => 'sunday', 'room_id' => null, 'errors' => []],
        $errors = [];

    public function changeTab($tab): void
    {
        $this->activeTab = $tab;
    }

    public function mount($group_id): void
    {
        $this->group = Group::with('groupTimes')->find($group_id);
        $this->getData();
        $this->setDayFlags($this->group);
    }

    public function checkRoom($day, $room_id)
    {
        $day = $this->$day;
        $this->reset('errors');
        $isRoomAvailable = $this->isRoomAvailable($day, $room_id);
    }

    public function clearSelectedRoom($day)
    {
        $this->$day['room_id'] = null;
    }


    public function render()
    {
        return view('livewire.group.group-time-form');
    }

    private function getData(): void
    {
        $currentBranchId = auth()->user()->current_branch_id;
        $this->rooms = Room::with('branches')->whereHas('branches', function ($query) use ($currentBranchId) {
            $query->where('branch_id', $currentBranchId);
        })
            ->where('status', 'available')->get();
        if ($this->group) {
            $groupTimes = $this->group->groupTimes;
            foreach ($groupTimes as $groupTime) {
                $day = $groupTime->name;
                $this->$day['start'] = Carbon::parse($groupTime->start)->format('H:i');
                $this->$day['end'] = Carbon::parse($groupTime->end)->format('H:i');
                $this->$day['room_id'] = $groupTime->room_id;
            }
        }
    }

    private function isRoomAvailable($day, $room_id)
    {
        $selectedDay = $day['name'];
        $this->$selectedDay['errors'] = [];

        if (empty($room_id) || empty($day['start']) || empty($day['end'])) {
            return true;
        }

        // Check for actual overlapping time slots, excluding back-to-back lessons
        $groupTimes = GroupTime::where('name', $day['name'])
            ->where('room_id', $room_id)
            ->where(function ($query) use ($day) {
                // Two time ranges overlap if:
                // new_start < existing_end AND new_end > existing_start
                // But exclude cases where they just touch at boundaries
                $query->where(function ($q) use ($day) {
                    $q->where('start', '<', $day['end'])
                        ->where('end', '>', $day['start']);
                });
            })
            ->when($this->group && $this->group->id, function ($query) {
                // Exclude current group's existing times from conflict check
                $query->whereHas('group', function ($q) {
                    $q->where('id', '!=', $this->group->id);
                });
            })
            ->count();

        if ($groupTimes > 0) {
            //translate it
            $this->$selectedDay['errors'][] = __('translation.groups.room_not_available');
            return false;
        }

        return true;
    }

    public function setDayFlags($group)
    {
        if ($group) {
            if ($group->hasOddDays() && $group->hasEvenDays()) {
                // If the group has both odd and even days
                $this->isOddDay = true;
                $this->isEvenDay = true;
            } elseif ($group->hasOddDays()) {
                // Only odd days
                $this->isOddDay = true;
            } elseif ($group->hasEvenDays()) {
                // Only even days
                $this->isEvenDay = true;
            }
            // Set Sunday flag if applicable
            if ($group->hasSunday()) {
                $this->isSunday = true;
            }
        } else {
            $this->isOddDay = true;
        }
    }
}
