<?php

namespace App\Livewire\Report\Leads;

use App\Models\LeadCall;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class UserCallsTable extends Component
{
    public $startDate;

    public $endDate;

    public function mount($startDate = null, $endDate = null): void
    {
        $this->startDate = $startDate ?? now()->startOfMonth()->toDateString();
        $this->endDate = $endDate ?? now()->endOfMonth()->toDateString();
    }

    public function render()
    {
        $userCallsData = $this->getUserCallsData();

        return view('livewire.report.leads.user-calls-table', [
            'userCallsData' => $userCallsData,
        ]);
    }

    private function getUserCallsData(): Collection
    {
        $data = DB::table('lead_calls as lc')
            ->leftJoin('users as u', 'u.id', '=', 'lc.causer_id')
            ->select([
                'lc.causer_id',
                'u.name as user_name',
                'u.email as user_email',
                DB::raw('COUNT(*) as total_calls'),
                DB::raw("SUM(CASE WHEN lc.status = 'answered' THEN 1 ELSE 0 END) as answered_calls"),
                DB::raw("SUM(CASE WHEN lc.status = 'not_answered' THEN 1 ELSE 0 END) as not_answered_calls"),
                DB::raw("SUM(CASE WHEN lc.status = 'busy' THEN 1 ELSE 0 END) as busy_calls"),
                DB::raw("SUM(CASE WHEN lc.status = 'declined' THEN 1 ELSE 0 END) as declined_calls"),
                DB::raw('COALESCE(SUM(lc.duration), 0) as total_duration'),
                DB::raw('COALESCE(AVG(lc.duration), 0) as average_duration'),
            ])
            ->whereNull('lc.deleted_at')
            ->whereBetween('lc.created_at', [
                $this->startDate.' 00:00:00',
                $this->endDate.' 23:59:59',
            ])
            ->groupBy('lc.causer_id', 'u.name', 'u.email')
            ->orderByDesc('total_calls')
            ->get();

        // Calculate answer rate in PHP
        return $data->map(function ($row) {
            $answerRate = $row->total_calls > 0
                ? round(($row->answered_calls / $row->total_calls) * 100, 1)
                : 0;

            return (object) array_merge((array) $row, [
                'answer_rate' => $answerRate,
                'formatted_total_duration' => LeadCall::formatDuration((int) $row->total_duration),
                'formatted_average_duration' => LeadCall::formatDuration((int) round($row->average_duration)),
            ]);
        });
    }
}
