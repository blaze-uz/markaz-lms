<?php

namespace App\Livewire\Group;

use App\Models\GroupTime;
use Livewire\Component;

class GroupCalendar extends Component
{
    public $group;

    public function mount($group): void
    {
        $this->group = $group;
    }

    public function render()
    {
        return view('livewire.group.group-calendar');
    }

    private function getData(): void
    {
        $startDate = now()->startOfMonth()->format('Y-m-d');
        $endDate = now()->endOfMonth()->format('Y-m-d');
        $colors = [
            'monday' => '#ff0000',
            'tuesday' => '#00ff00',
            'wednesday' => '#0000ff',
            'thursday' => '#ffff00',
            'friday' => '#ff00ff',
            'saturday' => '#00ffff',
            'sunday' => '#000000',
        ];

        $textColors = [
            'monday' => '#ffffff',
            'tuesday' => '#ffffff',
            'wednesday' => '#ffffff',
            'thursday' => '#000000',
            'friday' => '#ffffff',
            'saturday' => '#000000',
            'sunday' => '#ffffff',
        ];

        $this->events = [];

        // Loop through the days in the specified date range
        $currentDate = $startDate;
        while ($currentDate <= $endDate) {
            // Determine the day of the week (e.g., Monday, Tuesday) based on the date
            $dayOfWeek = date('l', strtotime($currentDate));

            // Retrieve the data for the specific day of the week
            $data = GroupTime::where('group_id', $this->group->id)
                ->where('name', strtolower($dayOfWeek)) // 'name' contains the day of the week
                ->first();

            if ($data) {
                // Format the data and add it to the events array
                $this->events[] = [
                    'title' => __('attr.' . $data->name),
                    'start' => "$currentDate $data->start:00",
                    'end' => "$currentDate $data->end:00",
                    'color' => $colors[$data->name],
                    'textColor' => $textColors[$data->name],
                ];
            }

            // Move to the next day
            $currentDate = date('Y-m-d', strtotime($currentDate . ' +1 day'));
        }
    }
}
