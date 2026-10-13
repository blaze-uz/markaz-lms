<?php

namespace App\Livewire\Dashboard;

use App\Models\Group;
use App\Models\GroupTime;
use App\Models\Room;
use Livewire\Component;

class DashboardCalendar extends Component
{
    public $day;
    public array $events;
    public array $resources = [];

    public function mount()
    {
        //get day's week day number
        $this->day = date('N');
        $this->events = $this->getCalendarData($this->day, $this->day);
        $this->resources = $this->getResources();
        $this->dispatch('refreshCalendar', events: $this->events, resources: $this->resources, day: date('Y-m-d', strtotime('monday this week +' . ($this->day - 1) . ' days')));
    }

    public function changeDay()
    {
        $this->events = $this->getCalendarData($this->day, $this->day);
        $this->dispatch('refreshCalendar', events: $this->events, resources: $this->resources, day: date('Y-m-d', strtotime('monday this week +' . ($this->day - 1) . ' days')));
    }

    public function render()
    {
        $resources = $this->getResources();

        return view('livewire.dashboard.dashboard-calendar', [
            'resources' => $resources,
        ]);
    }

    public function getCalendarData($start, $end): array
    {
        if ($start && $end) {
            //start and end is this week day number like 1, 2, 3 change it to date
            $start = date('Y-m-d', strtotime('monday this week +' . ($start - 1) . ' days'));
            $end = date('Y-m-d', strtotime('monday this week +' . ($end - 1) . ' days'));
        }
        $startDate = $start ?? now()->format('Y-m-d');
        $endDate = $end ?? now()->format('Y-m-d');

        $colors = [
            '#6A9C89',
            '#1AACAC',
            '#8294C4',
            '#5C5470',
            '#1AACAC',
            '#DFA67B',
            '#654E92',
            '#F44336',
            '#E91E63',
            '#9C27B0',
            '#673AB7',
            '#3F51B5',
            '#2196F3',
            '#03A9F4',
            '#00BCD4',
            '#009688',
            '#4CAF50',
        ];

        $events = [];

        // Loop through the days in the specified date range
        $currentDate = $startDate;
        while ($currentDate <= $endDate) {
            // Determine the day of the week (e.g., Monday, Tuesday) based on the date
            $dayOfWeek = date('l', strtotime($currentDate));

            // Retrieve the data for the specific day of the week
            $groups = Group::active()->select('id')->get();
            $data = GroupTime::with('group.teacher')->whereIn('group_id', $groups->pluck('id')->toArray())
                ->where('name', strtolower($dayOfWeek)) // 'name' contains the day of the week
                ->select(['id', 'room_id', 'group_id', 'start', 'end'])
                ->get();

            if ($data) {
                foreach ($data as $item) {
                    $events[] = [
                        'id' => rand(1, 100),
                        'resourceId' => $item->room_id,
                        'start' => "$currentDate $item->start:00",
                        'end' => "$currentDate $item->end:00",
                        'title' => $item->group?->name ?? '-' . ' - ' . $item->group?->teacher->name ?? '-',
                        'description' => $item->start . ' - ' . $item->end,
                        'color' => $colors[rand(0, 15)],
                        'textColor' => '#ffffff',
                    ];
                }
            }

            // Move to the next day
            $currentDate = date('Y-m-d', strtotime($currentDate . ' +1 day'));
        }
        return $events;
    }

    public function getResources(): array
    {
        return Room::select('id', 'name')
            ->get()
            ->map(function ($room) {
                return [
                    'id' => $room->id,
                    'title' => $room->name,
                ];
            })
            ->toArray();
    }
}
