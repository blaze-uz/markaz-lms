<?php

namespace App\Livewire\Report;

use App\Models\Student;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class StudentsAgeChart extends Component
{
    public function render()
    {
        $data = $this->getData();
        $labels = $this->getLabels($data);
        $series = $this->getSeries($data);
        return view('livewire.report.students-age-chart', [
            'labels' => $labels,
            'series' => $series,
        ]);
    }

    public function getData()
    {
        $data = [
            ['age' => '0-11', 'count' => 0],
            ['age' => '12-15', 'count' => 0],
            ['age' => '16-17', 'count' => 0],
            ['age' => '17-...', 'count' => 0],
        ];

        // cache the data for 1 day
        $students = Cache::remember('students_age_chart', now()->addDay(), function () {
            return Student::whereNotNull('birthday')->get('birthday');
        });

        foreach ($students as $student) {
            $age = Carbon::parse($student->birthday)->age;
            if ($age >= 11) {
                $data[0]['count']++;
            }
            if ($age >= 12 && $age <= 15) {
                $data[1]['count']++;
            }
            if ($age >= 16 && $age <= 17) {
                $data[2]['count']++;
            }
            if ($age >= 18) {
                $data[3]['count']++;
            }
        }

        return $data;
    }

    public function getLabels($data)
    {
        return collect($data)->pluck('age');
    }

    public function getSeries($data)
    {
        return collect($data)->pluck('count');
    }
}
