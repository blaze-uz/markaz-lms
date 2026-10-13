<?php

namespace App\Exports;

use App\Models\Payment;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PaymentsExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    public function __construct(
        private $filters
    ) {}

    public function query()
    {
        $filter = $this->filters;
        return Payment::search($filter['search'])
            ->with(['student'])
            ->whereBetween('date', [$filter['startDate'], $filter['endDate']])
            ->when($filter['paymentType'] !== 'all', fn($query) => $query->where('payment_type_id', $filter['paymentType']))
            ->when($filter['creator'] !== 'all', fn($query) => $query->where('created_by', $filter['creator']))
            ->when($filter['showRefunds'], fn($query) => $query->has('refunds'))
            ->when($filter['teacher'] !== 'all', function ($query) use ($filter) {
                return $query->whereHas('student_group_payment.group', function ($q) use ($filter) {
                    $q->where('teacher_id', $filter['teacher']);
                });
            })
            ->when($filter['group'] !== 'all', function ($query) use ($filter) {
                $query->where(function ($q) use ($filter) {
                    $q->where('group_id', $filter['group'])
                        ->orWhereHas('student_group_payment', function ($q) use ($filter) {
                            $q->where('group_id', $filter['group']);
                        });
                });
            })
            ->when($filter['studentGroupPaymentRange'] !== 'all', function ($query) use ($filter) {
                $query->whereHas('student_group_payment', function ($q) use ($filter) {
                    $q->where('range', $filter['studentGroupPaymentRange']);
                });
            })
            ->orderByDesc('date');
    }

    //    public function collection()
    //    {
    //        $filter = $this->filters;
    //        return Payment::search($filter['search'])
    //            ->with(['student'])
    //            ->whereIn('branch_id', auth()->user()->getBranchIds())
    //            ->whereBetween('date', [$filter['startDate'], $filter['endDate']])
    //            ->when($filter['paymentType'] !== 'all', fn($query) => $query->where('payment_type_id', $filter['paymentType']))
    //            ->when($filter['creator'] !== 'all', fn($query) => $query->where('created_by', $filter['creator']))
    //            ->when($filter['showRefunds'], fn($query) => $query->has('refunds'))
    //            ->orderByDesc('date')
    //            ->get();
    //    }

    public function headings(): array
    {
        return [
            '#',
            __('translation.students.full_name'),
            __('translation.students.phone'),
            __('translation.students.group'),
            __('translation.groups.debt_range'),
            __('translation.payments.type-name'),
            __('attr.amount'),
            __('translation.payments.discount'),
            __('translation.payments.created_by'),
            __('translation.payments.date'),
            __('translation.payments.is_refunded'),
            __('translation.payments.comment'),
        ];
    }

    public function map($payment): array
    {
        return [
            $payment->id,
            $payment->student?->getFullName(),
            '+998' . $payment->student?->phone,
            $payment->student_group_payment?->group?->name,
            $payment->student_group_payment?->range,
            $payment->payment_type?->name,
            $payment->amount,
            $payment->payment_discounts()->sum('amount'),
            $payment->creator?->name,
            dateFormat($payment->date, "Y.m.d H:i"),
            $payment->refunds()->count() ? 'Qaytarilgan' : '-',
            $payment->comment
        ];
    }
}
