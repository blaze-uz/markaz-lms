<?php

namespace App\Exports;

use App\Models\Student;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StudentsChartExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    use Exportable;

    public function __construct(
        private ?string $startDate,
        private ?string $endDate,
        private ?int $branchId = null
    ) {}

    public function collection()
    {
        $activeStudents = Student::query()
            ->when($this->startDate, function ($query) {
                $query->whereDate('created_at', '>=', $this->startDate);
            })
            ->when($this->endDate, function ($query) {
                $query->whereDate('created_at', '<=', $this->endDate);
            })
            ->whereRelation('branches', function ($query) {
                $query->where('branch_id', $this->branchId ?? auth()->user()->current_branch_id);
            })
            ->with(['groups', 'account'])
            ->get()
            ->map(function ($student) {
                $student->status = 'Added';
                return $student;
            });

        $deletedStudents = Student::onlyTrashed()
            ->when($this->startDate, function ($query) {
                $query->whereDate('deleted_at', '>=', $this->startDate);
            })
            ->when($this->endDate, function ($query) {
                $query->whereDate('deleted_at', '<=', $this->endDate);
            })
            ->whereRelation('branches', function ($query) {
                $query->where('branch_id', $this->branchId ?? auth()->user()->current_branch_id);
            })
            ->with(['groups', 'account'])
            ->get()
            ->map(function ($student) {
                $student->status = 'Removed';
                return $student;
            });

        return $activeStudents->concat($deletedStudents)->sortBy('created_at');
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
            'Status',
            __('attr.created_at'),
        ];
    }

    public function map($student): array
    {
        return [
            $student->id,
            $student->name ?? '-',
            $student->surname ?? '-',
            $student->phone ?? '-',
            $student->parent_phone ?? '-',
            $student->birthday ? Carbon::parse($student->birthday)->format('d.m.Y') : '-',
            $student->balance ?? 0,
            $student->groups->pluck('name')->join(', ') ?: '-',
            $student->status,
            $student->created_at->format('d.m.Y H:i'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 12,
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => [
                        'rgb' => 'E3F2FD',
                    ],
                ],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,   // ID
            'B' => 20,  // Name
            'C' => 20,  // Surname
            'D' => 15,  // Phone
            'E' => 15,  // Parent Phone
            'F' => 15,  // Birthday
            'G' => 12,  // Balance
            'H' => 30,  // Groups
            'I' => 12,  // Status
            'J' => 20,  // Created At
        ];
    }
}
