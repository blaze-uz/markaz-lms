<?php

namespace App\Exports;

use App\Models\Expense;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ExpensesExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    public function __construct(
        private $search,
        private $date,
        private $paymentType
    ) {}

    public function headings(): array
    {
        return [
            'ID',
            __('translation.expenses.item_name'),
            __('translation.expenses.quantity'),
            __('translation.expenses.price'),
            __('attr.payment_type'),
            __('translation.expenses.expense_account'),
            __('translation.expenses.created_by'),
        ];
    }

    public function query()
    {
        $query = Expense::query();
        try {
            $dateRange = explode(" - ", $this->date);
            if (count($dateRange) === 2) {
                $startDate = \Carbon\Carbon::createFromFormat('Y.m.d', trim($dateRange[0]))->startOfDay();
                $endDate = \Carbon\Carbon::createFromFormat('Y.m.d', trim($dateRange[1]))->endOfDay();
            } else {
                $startDate = now()->startOfDay();
                $endDate = now()->endOfDay();
            }
        } catch (\Exception $e) {
            $startDate = now()->startOfDay();
            $endDate = now()->endOfDay();
        }

        $query->search($this->search)
            ->with(['item', 'place', 'account', 'createdBy', 'paymentType'])
            ->whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate)
            ->when($this->paymentType !== 'all', function ($query) {
                $query->where('payment_type_id', $this->paymentType);
            });

        if (request()->creator) {
            $query->where('created_by', request()->creator);
        }

        return $query;
    }

    public function map($row): array
    {
        return [
            $row->id,
            $row->item?->name ?? '',
            $row->quantity,
            $row->price,
            $row->paymentType?->name ?? '',
            $row->account?->name ?? '',
            $row->createdBy?->name ?? '',
        ];
    }
}
