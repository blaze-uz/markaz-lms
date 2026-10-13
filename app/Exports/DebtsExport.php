<?php

namespace App\Exports;

use App\Models\StudentGroupPayment;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class DebtsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(
        private ?string $search = null,
        private ?string $groupId = null,
        private ?string $range = null,
        private ?string $teacherId = null
    ) {}

    public function query()
    {
        return StudentGroupPayment::query()
            ->select([
                'student_group_payments.*',
                'students.name',
                'students.surname',
                'students.phone',
                'groups.name as group_name'
            ])
            ->leftJoin('students', 'student_group_payments.student_id', '=', 'students.id')
            ->leftJoin('groups', 'student_group_payments.group_id', '=', 'groups.id')
            ->where('student_group_payments.left_amount', '>', 0)
            ->when($this->teacherId, function ($query) {
                $query->where('groups.teacher_id', $this->teacherId);
            })
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('students.name', 'ilike', '%' . $this->search . '%')
                        ->orWhere('students.surname', 'ilike', '%' . $this->search . '%')
                        ->orWhere('student_group_payments.id', 'ilike', '%' . $this->search . '%');
                });
            })
            ->when($this->groupId, fn($query) => $query->where('student_group_payments.group_id', $this->groupId))
            ->when($this->range, fn($query) => $query->where('student_group_payments.range', $this->range))
            ->orderBy('student_group_payments.id', 'desc');
    }

    public function headings(): array
    {
        return [
            '#',
            __('translation.student'),
            __('translation.phone'),
            __('translation.group'),
            __('translation.range'),
            __('translation.amount'),
            __('translation.left_amount'),
        ];
    }

    public function map($payment): array
    {
        return [
            $payment->id,
            $payment->student ? "{$payment->name} {$payment->surname}" : __('translation.student_not_found'),
            $payment->student?->phone ?? '-',
            $payment->group_name ?? '-',
            $payment->range ?? '-',
            $payment->amount,
            $payment->left_amount,
        ];
    }
}
