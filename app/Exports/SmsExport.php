<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SmsExport implements FromQuery, WithColumnWidths, WithHeadings, WithMapping, WithStyles
{
    use Exportable;

    protected $query;

    public function __construct(Builder $query)
    {
        $this->query = $query;
    }

    public function query()
    {
        return $this->query->with('smsable');
    }

    public function headings(): array
    {
        return [
            'ID',
            __('translation.sms.receiver_name'),
            __('translation.students.phone'),
            __('translation.sms.content'),
            __('attr.status'),
            __('translation.sms.price'),
            __('translation.sms.total_price'),
            __('translation.sms.character_count'),
            __('translation.sms.sms_count'),
            __('translation.sms.send_at'),
            __('attr.created_at'),
        ];
    }

    public function map($sms): array
    {
        $receiverName = '';
        if ($sms->smsable) {
            if (method_exists($sms->smsable, 'getFullName')) {
                $receiverName = $sms->smsable->getFullName();
            } else {
                $receiverName = ($sms->smsable->name ?? '').' '.($sms->smsable->surname ?? '');
            }
            $receiverName = trim($receiverName);
        }

        return [
            $sms->id,
            $receiverName ?: '-',
            $sms->phone ?? '-',
            $sms->content ?? '-',
            __('translation.sms.'.$sms->status) ?? $sms->status,
            $sms->price ? number_format($sms->price, 0, '.', ' ') : '-',
            $sms->total_price ? number_format($sms->total_price, 0, '.', ' ') : '-',
            $sms->character_count ?? '-',
            $sms->parts_count ?? '-',
            $sms->sent_at ? $sms->sent_at->format('d.m.Y H:i') : ($sms->updated_at ? $sms->updated_at->format('d.m.Y H:i') : '-'),
            $sms->created_at ? $sms->created_at->format('d.m.Y H:i') : '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 10,
            'B' => 25,
            'C' => 15,
            'D' => 50,
            'E' => 15,
            'F' => 15,
            'G' => 15,
            'H' => 15,
            'I' => 15,
            'J' => 18,
            'K' => 18,
        ];
    }
}
