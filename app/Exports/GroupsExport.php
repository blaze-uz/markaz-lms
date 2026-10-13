<?php

namespace App\Exports;

use App\Models\Group;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class GroupsExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    public function __construct(
        private $filters
    ) {}

    public function query()
    {
        $query = Group::query()
            ->join('branch_group', 'groups.id', '=', 'branch_group.group_id')
            ->join('branches', function ($join) {
                $join->on('branch_group.branch_id', '=', 'branches.id')
                    ->where('branches.id', auth()->user()->current_branch_id)
                    ->whereNull('branches.deleted_at');
            })
            ->whereNull('groups.deleted_at');

        if (auth()->user()->isTeacher()) {
            $query->where('groups.teacher_id', auth()->id());
        }

        if (!empty($this->filters['search'])) {
            $query->where(function ($query) {
                $query->where('groups.name', 'ilike', "%{$this->filters['search']}%")
                    ->when(!auth()->user()->isTeacher(), function ($q) {
                        $q->orWhereHas('course', function ($q) {
                            $q->where('name', 'ilike', "%{$this->filters['search']}%");
                        })
                            ->orWhereHas('teacher', function ($q) {
                                $q->where('name', 'ilike', "%{$this->filters['search']}%");
                            });
                    });
            });
        }

        if (!empty($this->filters['state'])) {
            $query->where('groups.state', $this->filters['state']);
        }

        if (!auth()->user()->isTeacher()) {
            if (!empty($this->filters['course'])) {
                $query->where('groups.course_id', $this->filters['course']);
            }

            if (!empty($this->filters['teacher'])) {
                $query->where('groups.teacher_id', $this->filters['teacher']);
            }
        }

        return $query->with([
            'course:id,name',
            'teacher:id,name',
        ])
            ->select([
                'groups.id',
                'groups.name',
                'groups.course_id',
                'groups.teacher_id',
                'groups.state',
                'groups.price'
            ])
            ->withCount('students')
            ->latest('groups.id');
    }

    public function headings(): array
    {
        return [
            'ID',
            __('translation.groups.name'),
            __('translation.courses.course'),
            __('translation.groups.teacher'),
            __('translation.status'),
            __('translation.groups.amount'),
            __('translation.users.students_count'),
        ];
    }

    public function map($group): array
    {
        return [
            $group->id,
            $group->name,
            $group->course?->name,
            $group->teacher?->name,
            __("translation.groups.{$group->state}"),
            money($group->price),
            $group->students_count,
        ];
    }
}
