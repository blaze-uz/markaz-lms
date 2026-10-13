<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class LeadsExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    protected $query;

    public function __construct(Builder $query)
    {
        $this->query = $query;
    }

    public function query()
    {
        return $this->query;
    }

    public function headings(): array
    {
        return [
            'ID',
            __('translation.leads.name'),
            __('translation.leads.phone'),
            __('translation.leads.chosen_days'),
            __('translation.leads.chosen_time'),
            __('translation.leads.teacher'),
            __('translation.leads.board'),
            __('translation.courses.course'),
            __('translation.leads.causer'),
            __('translation.leads.date'),
        ];
    }

    public function map($lead): array
    {
        return [
            $lead->id,
            $lead->name,
            $lead->phone,
            $lead->chosen_days ?: '-',
            $lead->chosen_time ?: '-',
            $lead->teacher?->name ?: '-',
            $lead->board?->name ?: '-',
            $lead->course?->name ?: '-',
            $lead->causer?->name ?: '-',
            $lead->created_at->format('d.m.Y H:i'),
        ];
    }
}
