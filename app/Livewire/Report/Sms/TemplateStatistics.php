<?php

namespace App\Livewire\Report\Sms;

use App\Models\Sms;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class TemplateStatistics extends Component
{
    public $startDate;
    public $endDate;

    public function mount($startDate = null, $endDate = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function render()
    {
        $query = Sms::query()
            ->select(
                'sms_templates.name as template_name',
                DB::raw('COUNT(*) as total_count'),
                DB::raw("SUM(CASE WHEN sms.status = 'sent' THEN 1 ELSE 0 END) as sent_count"),
                DB::raw("SUM(CASE WHEN sms.status = 'failed' THEN 1 ELSE 0 END) as failed_count"),
                DB::raw("SUM(CASE WHEN sms.status = 'pending' THEN 1 ELSE 0 END) as pending_count")
            )
            ->leftJoin('sms_templates', 'sms.sms_template_id', '=', 'sms_templates.id')
            ->whereNotNull('sms_template_id')
            ->groupBy('sms_template_id', 'sms_templates.name')
            ->orderByDesc(DB::raw("SUM(CASE WHEN sms.status = 'sent' THEN 1 ELSE 0 END)"));

        if ($this->startDate) {
            $query->whereDate('sms.created_at', '>=', $this->startDate);
        }
        if ($this->endDate) {
            $query->whereDate('sms.created_at', '<=', $this->endDate);
        }

        $statistics = $query->get();

        // Calculate success rate for each template
        $statistics->transform(function ($item) {
            $item->success_rate = $item->total_count > 0
                ? round(($item->sent_count / $item->total_count) * 100, 1)
                : 0;
            return $item;
        });

        return view('livewire.report.sms.template-statistics', [
            'statistics' => $statistics
        ]);
    }
}
