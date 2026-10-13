<?php

namespace App\Livewire\Dashboard;

use App\Models\GroupTime;
use App\Models\Room;
use Livewire\Attributes\On;
use Livewire\Component;

class RoomCalendar extends Component
{
    public $selectedRoomId, $selectedRoom, $groupTimes;

    public function mount()
    {
        $this->updateSelectedRoom(Room::first()->id ?? 0);
    }

    #[On('select-room')]
    public function updateSelectedRoom($room_id = null): void
    {
        if ($room_id) {
            $this->selectedRoomId = $room_id;
            $this->selectedRoom = Room::findOrFail($room_id);

            // Fetch group times
            $groupTimes = GroupTime::with('group.teacher')
                ->where('room_id', $room_id)
                ->orderBy('start')
                ->get();

            // Define an array to hold grouped data with all weekdays
            $weekdays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
            $groupedTimes = array_fill_keys($weekdays, []);

            // Group groupTimes by day of the week
            foreach ($groupTimes as $time) {
                if ($time->group == null) {
                    continue;
                }
                // Get the day of the week for the current group time

                // Append the current group time data to the day of the week
                $groupedTimes[$time->name][] = [
                    'group' => $time->group->name,
                    'group_id' => $time->group->id,
                    'teacher' => $time->group->teacher?->name ?? 'No teacher assigned',
                    'start' => $time->start,
                    'end' => $time->end,
                ];
            }

            $this->groupTimes = $groupedTimes;
        } else {
            $this->selectedRoom = null;
            $this->selectedRoomId = null;
        }

        $this->dispatch('groupChanged');
    }

    public function render()
    {
        $rooms = Room::orderBy('name')->get();

        return view('livewire.dashboard.room-calendar', [
            'rooms' => $rooms,
        ]);
    }
}
