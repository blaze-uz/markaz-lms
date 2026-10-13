<?php

namespace App\Livewire\Report;

use App\Models\Group;
use App\Models\Student;
use Livewire\Component;

class TelegramConnectionStats extends Component
{
    public $isExpanded = false;

    public ?int $modalGroupId = null;

    public $sortField = 'connection_percentage';

    public $sortDirection = 'desc';

    public function openStudentModal(int $groupId): void
    {
        $this->modalGroupId = $groupId;
    }

    public function closeStudentModal(): void
    {
        $this->modalGroupId = null;
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'desc';
        }
    }

    public function toggle()
    {
        $this->isExpanded = ! $this->isExpanded;

        if (! $this->isExpanded) {
            $this->modalGroupId = null;
        }
    }

    public function placeholder()
    {
        return view('livewire.placeholders.skeleton');
    }

    public function render()
    {
        // Overall stats
        $totalStudents = Student::whereHas('branches', function ($query) {
            $query->where('branch_id', auth()->user()->current_branch_id);
        })->count();

        $connectedStudents = Student::whereHas('branches', function ($query) {
            $query->where('branch_id', auth()->user()->current_branch_id);
        })
            ->whereNotNull('telegram_chat_id')
            ->count();

        $notConnectedStudents = $totalStudents - $connectedStudents;

        $connectionPercentage = $totalStudents > 0
            ? round(($connectedStudents / $totalStudents) * 100, 2)
            : 0;

        // Group-wise statistics
        $groupStats = Group::whereHas('branches', function ($query) {
            $query->where('branch_id', auth()->user()->current_branch_id);
        })
            ->withCount([
                'students as total_students',
                'students as connected_students' => function ($query) {
                    $query->whereNotNull('telegram_chat_id');
                },
            ])
            ->get()
            ->filter(function ($group) {
                return $group->total_students > 0;
            })
            ->map(function ($group) {
                $group->not_connected_students = $group->total_students - $group->connected_students;
                $group->connection_percentage = $group->total_students > 0
                    ? round(($group->connected_students / $group->total_students) * 100, 2)
                    : 0;

                return $group;
            })
            ->sortBy($this->sortField, SORT_REGULAR, $this->sortDirection === 'desc')
            ->values();

        // Chart data for pie/donut chart
        $chartData = [
            'labels' => [__('translation.telegram.connected'), __('translation.telegram.not_connected')],
            'series' => [$connectedStudents, $notConnectedStudents],
            'colors' => ['#09b29c', '#ef6547'],
        ];

        // Top 5 groups by connection percentage
        $topGroups = $groupStats->sortByDesc('connection_percentage')->take(5);

        // Bottom 5 groups by connection percentage
        $bottomGroups = $groupStats->sortBy('connection_percentage')->take(5);

        $modalGroupTitle = '';
        $modalGroupStudents = collect();
        if ($this->modalGroupId !== null) {
            $modalGroup = Group::query()
                ->whereHas('branches', function ($query) {
                    $query->where('branch_id', auth()->user()->current_branch_id);
                })
                ->whereKey($this->modalGroupId)
                ->first();

            if ($modalGroup !== null) {
                $modalGroupTitle = $modalGroup->name;
                $modalGroupStudents = $modalGroup->students()
                    ->select(['students.id', 'students.name', 'students.surname', 'students.telegram_chat_id'])
                    ->orderBy('students.surname')
                    ->orderBy('students.name')
                    ->get();
            }
        }

        return view('livewire.report.telegram-connection-stats', [
            'totalStudents' => $totalStudents,
            'connectedStudents' => $connectedStudents,
            'notConnectedStudents' => $notConnectedStudents,
            'connectionPercentage' => $connectionPercentage,
            'chartData' => $chartData,
            'groupStats' => $groupStats,
            'topGroups' => $topGroups,
            'bottomGroups' => $bottomGroups,
            'modalGroupTitle' => $modalGroupTitle,
            'modalGroupStudents' => $modalGroupStudents,
        ]);
    }
}
