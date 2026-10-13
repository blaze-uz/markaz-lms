<?php

namespace App\Livewire;

use App\Models\GroupStudentLog;
use Carbon\Carbon;
use Livewire\Component;
use Illuminate\Support\Facades\Log;

class TeacherStudentStatisticsByMonth extends Component
{
    public $user;
    public $monthsData = [];
    public $monthCount = 6;
    public $monthOptions = [6, 12, 24];

    // Properties for modal
    public $selectedMonth;
    public $selectedMonthFormatted;
    public $selectedMonthStudents = [];

    public function mount($user)
    {
        $this->user = $user;
        $this->updateStats();
    }

    public function updateStats()
    {
        $this->monthsData = $this->getStudentsCountPerMonth($this->monthCount);
    }

    public function setMonthCount($count)
    {
        $this->monthCount = (int)$count;
        $this->updateStats();

        // If modal is open, refresh its data with the new month count
        if ($this->selectedMonth) {
            $this->loadStudentsForMonth($this->selectedMonth);
        }
    }

    /**
     * Load students for a specific month and show in modal
     */
    public function loadStudentsForMonth($yearMonth)
    {
        // Parse the year-month
        list($year, $month) = explode('-', $yearMonth);
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth();

        // Set the selected month for display
        $this->selectedMonth = $yearMonth;
        $this->selectedMonthFormatted = Carbon::createFromDate($year, $month, 1)->format('F Y');

        // Use the SAME date range as the main table (from monthCount months ago)
        $tableStartDate = Carbon::now()->subMonths($this->monthCount);

        // Get ALL logs for this teacher (no date restriction) to properly detect transfers
        $allLogs = GroupStudentLog::with(['student' => function ($query) {
            $query->withTrashed();
        }, 'group'])
            ->whereHas('group', function ($query) {
                $query->where('teacher_id', $this->user->id);
            })
            ->orderBy('date')
            ->get();

        // Process all logs to identify transfers vs actual joins/leaves
        $allProcessedData = $this->processLogsForTransfers($allLogs);

        // First filter by table date range (same as main table), then by selected month
        $tableFilteredData = [
            'actual_joined' => collect($allProcessedData['actual_joined'])
                ->filter(function ($student) use ($tableStartDate) {
                    return Carbon::parse($student['date_sort'])->gte($tableStartDate);
                }),
            'actual_left' => collect($allProcessedData['actual_left'])
                ->filter(function ($student) use ($tableStartDate) {
                    return Carbon::parse($student['date_sort'])->gte($tableStartDate);
                }),
            'transferred' => collect($allProcessedData['transferred'])
                ->filter(function ($student) use ($tableStartDate) {
                    return Carbon::parse($student['date_sort'])->gte($tableStartDate);
                }),
        ];

        // Then filter to only include entries from the selected month
        $selectedMonthData = [
            'actual_joined' => $tableFilteredData['actual_joined']
                ->filter(function ($student) use ($startDate, $endDate) {
                    $studentDate = Carbon::parse($student['date_sort']);
                    return $studentDate->between($startDate, $endDate);
                })
                ->values()
                ->toArray(),
            'actual_left' => $tableFilteredData['actual_left']
                ->filter(function ($student) use ($startDate, $endDate) {
                    $studentDate = Carbon::parse($student['date_sort']);
                    return $studentDate->between($startDate, $endDate);
                })
                ->values()
                ->toArray(),
            'transferred' => $tableFilteredData['transferred']
                ->filter(function ($student) use ($startDate, $endDate) {
                    $studentDate = Carbon::parse($student['date_sort']);
                    return $studentDate->between($startDate, $endDate);
                })
                ->values()
                ->toArray(),
        ];

        $this->selectedMonthStudents = $selectedMonthData;
    }

    private function processLogsForTransfers($logs)
    {
        $actualJoined = [];
        $actualLeft = [];
        $transferred = [];

        // Group logs by student
        $studentLogs = $logs->groupBy('student_id');

        foreach ($studentLogs as $studentId => $studentLogEntries) {
            // Sort by date
            $sortedLogs = $studentLogEntries->sortBy('date')->values();

            // Track processed indices
            $processedIndices = [];

            // First pass: Identify transfers (detach followed by attach within reasonable time)
            for ($i = 0; $i < count($sortedLogs) - 1; $i++) {
                if (in_array($i, $processedIndices)) {
                    continue;
                }

                $currentLog = $sortedLogs[$i];

                if ($currentLog->action === 'detach') {
                    // Look for the next attach action for this student
                    for ($j = $i + 1; $j < count($sortedLogs); $j++) {
                        if (in_array($j, $processedIndices)) {
                            continue;
                        }

                        $nextLog = $sortedLogs[$j];

                        if ($nextLog->action === 'attach') {
                            // Check if it's within reasonable time (extended to 24 hours for flexibility)
                            $timeDiff = Carbon::parse($nextLog->date)->diffInHours(Carbon::parse($currentLog->date));

                            if ($timeDiff <= 24) {
                                // This is a transfer
                                $student = $currentLog->student;
                                $isDeleted = $student && $student->deleted_at !== null;

                                $transferred[] = [
                                    'id' => $studentId,
                                    'name' => $student ? $student->name . ' ' . ($student->surname ?? '') : 'Unknown',
                                    'date' => Carbon::parse($currentLog->date)->format('Y-m-d H:i'),
                                    'date_sort' => $currentLog->date,
                                    'is_deleted' => $isDeleted,
                                    'deleted_at' => $isDeleted ? Carbon::parse($student->deleted_at)->format('Y-m-d H:i') : null,
                                    'from_group_id' => $currentLog->group_id,
                                    'from_group_name' => $currentLog->group ? $currentLog->group->name : 'Unknown',
                                    'to_group_id' => $nextLog->group_id,
                                    'to_group_name' => $nextLog->group ? $nextLog->group->name : 'Unknown',
                                ];

                                // Mark both logs as processed
                                $processedIndices[] = $i;
                                $processedIndices[] = $j;
                                break; // Found the matching attach, move to next detach
                            }
                        }
                    }
                }
            }

            // Second pass: Process remaining unmatched logs
            for ($i = 0; $i < count($sortedLogs); $i++) {
                if (in_array($i, $processedIndices)) {
                    continue; // Skip already processed logs
                }

                $currentLog = $sortedLogs[$i];
                $student = $currentLog->student;
                $isDeleted = $student && $student->deleted_at !== null;

                $baseStudentInfo = [
                    'id' => $studentId,
                    'name' => $student ? $student->name . ' ' . ($student->surname ?? '') : 'Unknown',
                    'date' => Carbon::parse($currentLog->date)->format('Y-m-d H:i'),
                    'date_sort' => $currentLog->date,
                    'is_deleted' => $isDeleted,
                    'deleted_at' => $isDeleted ? Carbon::parse($student->deleted_at)->format('Y-m-d H:i') : null,
                ];

                if ($currentLog->action === 'detach') {
                    // This is an actual leave (student truly left, not transferred)
                    $actualLeft[] = array_merge($baseStudentInfo, [
                        'group_id' => $currentLog->group_id,
                        'group_name' => $currentLog->group ? $currentLog->group->name : 'Unknown',
                    ]);
                } elseif ($currentLog->action === 'attach') {
                    // This is an actual join (new student joining, not from transfer)
                    $actualJoined[] = array_merge($baseStudentInfo, [
                        'group_id' => $currentLog->group_id,
                        'group_name' => $currentLog->group ? $currentLog->group->name : 'Unknown',
                    ]);
                }
            }
        }

        return [
            'actual_joined' => collect($actualJoined)->sortByDesc('date_sort')->values()->toArray(),
            'actual_left' => collect($actualLeft)->sortByDesc('date_sort')->values()->toArray(),
            'transferred' => collect($transferred)->sortByDesc('date_sort')->values()->toArray(),
        ];
    }

    public function render()
    {
        return view('livewire.teacher-student-statistics-by-month');
    }

    /**
     * Get students added and removed count per month for the teacher
     * @param int $months Number of months to look back
     * @return array
     */
    public function getStudentsCountPerMonth($months)
    {
        $startDate = Carbon::now()->subMonths($months);

        // Get ALL logs for this teacher (no date restriction) to properly detect transfers
        // We need all historical data to identify transfers correctly
        $allLogs = GroupStudentLog::whereHas('group', function ($query) {
            $query->where('teacher_id', $this->user->id);
        })
            ->orderBy('date')
            ->get();

        // Process all logs to identify transfers vs actual joins/leaves
        $allProcessedData = $this->processLogsForTransfers($allLogs);

        // Filter processed data to only include entries within the selected time period
        $filteredProcessedData = [
            'actual_joined' => collect($allProcessedData['actual_joined'])
                ->filter(function ($student) use ($startDate) {
                    return Carbon::parse($student['date_sort'])->gte($startDate);
                }),
            'actual_left' => collect($allProcessedData['actual_left'])
                ->filter(function ($student) use ($startDate) {
                    return Carbon::parse($student['date_sort'])->gte($startDate);
                }),
            'transferred' => collect($allProcessedData['transferred'])
                ->filter(function ($student) use ($startDate) {
                    return Carbon::parse($student['date_sort'])->gte($startDate);
                }),
        ];

        // Group the filtered data by month
        $monthlyGroupedData = [
            'actual_joined' => $filteredProcessedData['actual_joined']->groupBy(function ($student) {
                return Carbon::parse($student['date_sort'])->format('Y-m');
            }),
            'actual_left' => $filteredProcessedData['actual_left']->groupBy(function ($student) {
                return Carbon::parse($student['date_sort'])->format('Y-m');
            }),
            'transferred' => $filteredProcessedData['transferred']->groupBy(function ($student) {
                return Carbon::parse($student['date_sort'])->format('Y-m');
            }),
        ];

        $monthlyStats = [];

        // Initialize all months with zero counts
        for ($i = $months - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $yearMonth = $date->format('Y-m');
            $monthlyStats[$yearMonth] = [
                'actual_joined' => 0,
                'actual_left' => 0,
                'transferred' => 0,
                'month' => __('translation.months.' . $date->format('F')) . ' ' . $date->format('Y'),
                'month_num' => $date->format('n'),
                'year' => $date->format('Y')
            ];
        }

        // Fill in the actual counts from grouped data
        foreach ($monthlyStats as $yearMonth => $monthData) {
            $monthlyStats[$yearMonth]['actual_joined'] = $monthlyGroupedData['actual_joined']->get($yearMonth, collect())->count();
            $monthlyStats[$yearMonth]['actual_left'] = $monthlyGroupedData['actual_left']->get($yearMonth, collect())->count();
            $monthlyStats[$yearMonth]['transferred'] = $monthlyGroupedData['transferred']->get($yearMonth, collect())->count();
        }

        // Sort by year-month in descending order (newest first)
        krsort($monthlyStats);

        return array_values($monthlyStats);
    }
}
