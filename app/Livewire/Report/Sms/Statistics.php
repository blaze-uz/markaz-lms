<?php

namespace App\Livewire\Report\Sms;

use App\Models\Sms;
use App\Traits\HasDateRange;
use Carbon\Carbon;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class Statistics extends Component
{
    use HasDateRange;

    public function mount($startDate = null, $endDate = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        if ($startDate && $endDate) {
            $this->date = "$startDate - $endDate";
        }
    }

    public function render()
    {
        $query = Sms::query()
            ->select(
                DB::raw('DATE(created_at) as date'),
                'status',
                DB::raw('count(*) as count')
            )
            ->groupBy('date', 'status')
            ->orderBy('date');

        if ($this->startDate) {
            $query->whereDate('created_at', '>=', $this->startDate);
        }
        if ($this->endDate) {
            $query->whereDate('created_at', '<=', $this->endDate);
        }

        $data = $query->get();

        // Get all dates in range
        $dates = collect();
        if ($data->count() > 0) {
            $currentDate = $data->min('date');
            $lastDate = $data->max('date');
            while ($currentDate <= $lastDate) {
                $dates->push($currentDate);
                $currentDate = date('Y-m-d', strtotime($currentDate . ' +1 day'));
            }
        }

        $series = [];
        $statuses = ['sent', 'pending', 'failed', 'scheduled'];
        foreach ($statuses as $status) {
            $statusData = [];
            foreach ($dates as $date) {
                $count = $data->where('date', $date)->where('status', $status)->first()?->count ?? 0;
                $statusData[] = $count;
            }
            $series[] = [
                'name' => __("translation.sms.$status"),
                'data' => $statusData
            ];
        }

        return view('livewire.report.sms.statistics', [
            'dates' => $dates->map(fn($date) => date('d.m.Y', strtotime($date))),
            'series' => $series
        ]);
    }
}
