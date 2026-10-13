<?php

namespace App\Exports;

use App\Models\Student;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class StudentsExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    public function __construct(
        private $filter
    ) {
    }

    public function headings(): array
    {
        return [
            'ID',
            __('translation.students.name'),
            __('translation.students.surname'),
            __('translation.students.phone'),
            __('translation.students.parent_phone'),
            __('translation.students.birthday'),
            __('translation.students.balance'),
            __('translation.groups.groups'),
            __('attr.created_at'),
        ];
    }

    public function query()
    {
        $query = Student::query();
        if (auth()->user()->hasRole('Teacher')) {
            $groupIds = auth()->user()->groups()->pluck('id')->toArray();
            $query->where(function ($query) use ($groupIds) {
                $query->whereHas('groups', function ($teacherQuery) use ($groupIds) {
                    return $teacherQuery->whereIn('group_id', $groupIds);
                })->orWhere('created_by_id', auth()->id());
            });
        } elseif (! isset($this->filter['showAllBranches']) || ! $this->filter['showAllBranches']) {
            // Apply branch filter only if not showing all branches and not a teacher
            $currentBranchId = auth()->user()->current_branch_id;

            if ($currentBranchId) {
                $query->whereHas('branches', function ($branchQuery) use ($currentBranchId) {
                    $branchQuery->where('branch_id', $currentBranchId);
                });
            }
        }
        // When showing all branches, we don't apply any branch filter
        // This allows exports to include students from all branches

        if ($this->filter['state']) {
            if ($this->filter['state'] === 'activeStudentsCount') {
                $query->whereNull('deleted_at')->has('groups');
            } elseif ($this->filter['state'] === 'debtStudentsCount') {
                $query->whereHas('groupPayments', function ($q) {
                    $q->where('left_amount', '>', 0);
                });
            } elseif ($this->filter['state'] === 'unattachedStudentsCount') {
                $query->whereDoesntHave('groups');
            } elseif ($this->filter['state'] === 'deletedStudentsCount') {
                $query->onlyTrashed();
            }
        }

        if ($this->filter['status']) {
            if ($this->filter['status'] == 'active') {
                $query->whereNull('deleted_at');
            } elseif ($this->filter['status'] == 'inactive') {
                $query->onlyTrashed();
            } elseif ($this->filter['status'] == 'debt') {
                $query->whereRelation('groupPayments', 'left_amount', '!=', 0);
            }
        }

        if ($this->filter['search']) {
            if ($this->filter['status'] != 'inactive') {
                if ($this->filter['status'] == 'inactive') {
                    $query->withTrashed();
                }
                $search = $this->filter['search'];
                $query->where(fn ($q) => $q->whereRaw("CONCAT(name, ' ', surname) ilike ?", ["%$search%"])
                    ->orWhereRaw("CONCAT(surname, ' ', name) ilike ?", ["%$search%"]));
            }
        }

        if ($this->filter['group_id']) {
            if ($this->filter['group_id'] == 'null') {
                $query->whereDoesntHave('groups');
            } else {
                $query->whereRelation('groups', 'group_id', $this->filter['group_id']);
            }
        }

        if (! empty($this->filter['teacher_id']) && ! auth()->user()->hasRole('Teacher')) {
            $query->whereHas('groups', function ($query) {
                $query->where('teacher_id', $this->filter['teacher_id']);
            });
        }

        return $query->with(['groups' => function ($query) {
            $query->with('course:id,name');
        }])->orderBy('created_at', 'desc');
    }

    public function map($row): array
    {
        // Get all student groups as a comma-separated string
        $groups = $row->groups->map(function ($group) {
            $courseName = $group->course?->name ? " ({$group->course->name})" : ' (-)';

            return $group->name.$courseName;
        })->filter()->implode(', ');

        return [
            $row->id,
            $row->name,
            $row->surname,
            $row->phone, // Ensure this function returns valid phone numbers
            $row->parent_phone,
            $row?->birthday,
            $row->balance ?? 0,
            $groups,
            $row->created_at?->format('d.m.Y H:i:s'),
        ];
    }
}
