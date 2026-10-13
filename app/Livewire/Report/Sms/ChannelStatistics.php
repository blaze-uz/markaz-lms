<?php

namespace App\Livewire\Report\Sms;

use App\Models\Sms;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class ChannelStatistics extends Component
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
                'channel',
                DB::raw('COUNT(*) as total_count'),
                DB::raw("SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) as sent_count"),
                DB::raw("SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed_count"),
                DB::raw("SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_count")
            )
            ->groupBy('channel')
            ->orderByDesc(DB::raw("SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END)"));

        if ($this->startDate) {
            $query->whereDate('created_at', '>=', $this->startDate);
        }
        if ($this->endDate) {
            $query->whereDate('created_at', '<=', $this->endDate);
        }

        $statistics = $query->get();

        // Calculate success rate for each channel
        $statistics->transform(function ($item) {
            $item->success_rate = $item->total_count > 0
                ? round(($item->sent_count / $item->total_count) * 100, 1)
                : 0;
            return $item;
        });

        return view('livewire.report.sms.channel-statistics', [
            'statistics' => $statistics
        ]);
    }
}
