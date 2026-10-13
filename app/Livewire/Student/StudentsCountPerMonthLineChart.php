<?php

namespace App\Livewire\Student;

use App\Models\Student;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class StudentsCountPerMonthLineChart extends Component
{
    public function render()
    {
        $series = $this->getData();
        return view('livewire.student.students-count-per-month-line-chart', [
            'series' => $series
        ]);
    }

    private function getData()
    {
        // Initialize the series array
        $series = [];

        // Get the current year
        $currentYear = date('Y');

        // Generate months for the current year
        $months = [];
        for ($i = 1; $i <= 12; $i++) {
            $month = str_pad($i, 2, '0', STR_PAD_LEFT); // Zero-padding month
            $months[] = $currentYear . '-' . $month;
        }

        // Cache key prefix
        $cacheKeyPrefix = 'user_counts_';

        // Retrieve user counts for each month
        foreach ($months as $month) {
            $cacheKey = $cacheKeyPrefix . $month;
            $count = Cache::remember($cacheKey, now()->addHours(1), function () use ($month) {
                return $this->getUserCountForMonth($month);
            });

            $series[] = $count;
        }

        return $series;
    }

    private function getUserCountForMonth(string $month)
    {
        // Get the current year and month
        $currentYear = date('Y');
        $currentMonth = date('m');

        // Extract year and month from the given month string
        $givenYear = substr($month, 0, 4);
        $givenMonth = substr($month, 5);

        // If the given year is greater than the current year or
        // if the given year is the same but the given month is greater than the current month,
        // return null
        if ($givenYear > $currentYear || ($givenYear == $currentYear && $givenMonth > $currentMonth)) {
            return null;
        }

        // Calculate the last day of the month
        $lastDayOfMonth = date('Y-m-t', strtotime($month));

        // Retrieve the student count until the last day of the month
        return Student::where('created_at', '<=', $lastDayOfMonth . ' 23:59:59')->count();
    }
}
