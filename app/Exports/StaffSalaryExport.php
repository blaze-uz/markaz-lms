<?php

namespace App\Exports;

use App\Models\StaffSalary;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class StaffSalaryExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    public function __construct(
        private $filters
    )
    {
    }

    public function query()
    {
        return StaffSalary::when($this->filters['staff'], function ($query) {
            $query->where('staff_id', $this->filters['staff']);
        })
            ->when($this->filters['month'], function ($query) {
                $query->whereMonth('date', $this->filters['month']);
            })
            ->whereRelation('staff', function ($query) {
                $query->whereHas('branches', function ($query) {
                    $query->where('branch_id', auth()->user()->current_branch_id);
                });
            })
            ->withCount('transactions')
            ->with('staff')
            ->latest('id');
    }

    public function headings(): array
    {
        return [
            '#',
            __('translation.users.name'),
            __('translation.users.amount'),
            __('translation.users.given_amount'),
            __('translation.users.which_month'),
            __('translation.users.date'),
        ];
    }

    public function map($salary): array
    {
        $salary->amount = $salary->transactions_count > 0 ? $salary->amount : 0;
        $salary->given_amount = $salary->transactions_count > 0 ? $salary->given_amount : 0;

        return [
            $salary->id,
            $salary->staff?->name ?? '-',
            $salary->amount,
            $salary->given_amount,
            dateFormat($salary->date, 'F'),
            dateFormat($salary->created_at)
        ];
    }
}
