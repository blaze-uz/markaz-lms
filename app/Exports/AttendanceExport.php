<?php

namespace App\Exports;

use App\Models\Attendance;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AttendanceExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    use Exportable;

    public function __construct(
        private $startDate,
        private $endDate,
        private $branchId = null
    ) {}

    public function collection()
    {
        $query = Attendance::whereDate('date', '>=', $this->startDate)
            ->whereDate('date', '<=', $this->endDate)
            ->with(['student', 'group'])
            ->whereRelation('student', function ($query) {
                $query->whereHas('branches', function ($query) {
                    $query->where('branch_id', $this->branchId ?? auth()->user()->current_branch_id);
                });
            });

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            __('translation.students.name'),
            __('translation.students.surname'),
            __('translation.groups.groups'),
            __('attr.date'),
            __('attr.status'),
            __('attr.created_at'),
        ];
    }

    public function map($attendance): array
    {
        return [
            $attendance->id,
            $attendance->student->name ?? '-',
            $attendance->student->surname ?? '-',
            $attendance->group->name ?? '-',
            $attendance->date->format('d.m.Y'),
            $attendance->status->value === 'present' ? __('attr.present') : __('attr.absent'),
            $attendance->created_at->format('d.m.Y H:i'),
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
            'D' => 25,  // Group
            'E' => 15,  // Date
            'F' => 15,  // Status
            'G' => 20,  // Created At
        ];
    }
}
