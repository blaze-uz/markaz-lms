<?php

namespace App\Livewire\Report\Leads;

use App\Models\Lead;
use App\Models\LeadCall;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class OperatorResponsibilityTable extends Component
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
        return view('livewire.report.leads.operator-responsibility-table', [
            'operatorRows' => $this->getOperatorRows(),
        ]);
    }

    private function getOperatorRows(): Collection
    {
        $startAt = $this->startDate.' 00:00:00';
        $endAt = $this->endDate.' 23:59:59';

        $leadQuery = Lead::withTrashed()
            ->whereBetween('leads.created_at', [$startAt, $endAt]);

        $leadIds = (clone $leadQuery)->pluck('leads.id');

        $responsibilityRows = (clone $leadQuery)
            ->leftJoin('users as u', 'u.id', '=', 'leads.causer_id')
            ->select([
                'leads.causer_id as user_id',
                'u.name as user_name',
                'u.email as user_email',
                DB::raw('COUNT(*) as responsible_leads'),
                DB::raw("SUM(CASE WHEN leads.student_id IS NOT NULL OR leads.status = 'student' THEN 1 ELSE 0 END) as student_leads"),
            ])
            ->groupBy('leads.causer_id', 'u.name', 'u.email')
            ->get()
            ->keyBy('user_id');

        $callRows = DB::table('lead_calls as lc')
            ->leftJoin('users as u', 'u.id', '=', 'lc.causer_id')
            ->select([
                'lc.causer_id as user_id',
                'u.name as user_name',
                'u.email as user_email',
                DB::raw('COUNT(DISTINCT lc.lead_id) as contacted_leads'),
                DB::raw('COUNT(*) as total_calls'),
                DB::raw("SUM(CASE WHEN lc.status = 'answered' THEN 1 ELSE 0 END) as answered_calls"),
                DB::raw("SUM(CASE WHEN lc.status = 'not_answered' THEN 1 ELSE 0 END) as not_answered_calls"),
                DB::raw("SUM(CASE WHEN lc.status = 'busy' THEN 1 ELSE 0 END) as busy_calls"),
                DB::raw("SUM(CASE WHEN lc.status = 'declined' THEN 1 ELSE 0 END) as declined_calls"),
                DB::raw('COALESCE(SUM(lc.duration), 0) as total_duration'),
                DB::raw('COALESCE(AVG(lc.duration), 0) as average_duration'),
            ])
            ->whereNull('lc.deleted_at')
            ->whereIn('lc.lead_id', $leadIds)
            ->whereBetween('lc.created_at', [$startAt, $endAt])
            ->groupBy('lc.causer_id', 'u.name', 'u.email')
            ->get()
            ->keyBy('user_id');

        return $responsibilityRows
            ->keys()
            ->merge($callRows->keys())
            ->unique()
            ->map(function ($userId) use ($responsibilityRows, $callRows) {
                $responsibility = $responsibilityRows->get($userId);
                $calls = $callRows->get($userId);
                $responsibleLeads = (int) ($responsibility->responsible_leads ?? 0);
                $studentLeads = (int) ($responsibility->student_leads ?? 0);
                $conversionRate = $responsibleLeads > 0
                    ? round(($studentLeads / $responsibleLeads) * 100, 1)
                    : 0;

                return (object) [
                    'user_id' => $userId,
                    'user_name' => $responsibility->user_name ?? $calls->user_name ?? null,
                    'user_email' => $responsibility->user_email ?? $calls->user_email ?? null,
                    'responsible_leads' => $responsibleLeads,
                    'contacted_leads' => (int) ($calls->contacted_leads ?? 0),
                    'total_calls' => (int) ($calls->total_calls ?? 0),
                    'answered_calls' => (int) ($calls->answered_calls ?? 0),
                    'not_answered_calls' => (int) ($calls->not_answered_calls ?? 0),
                    'busy_calls' => (int) ($calls->busy_calls ?? 0),
                    'declined_calls' => (int) ($calls->declined_calls ?? 0),
                    'student_leads' => $studentLeads,
                    'conversion_rate' => $conversionRate,
                    'formatted_total_duration' => LeadCall::formatDuration((int) ($calls->total_duration ?? 0)),
                    'formatted_average_duration' => LeadCall::formatDuration((int) round($calls->average_duration ?? 0)),
                ];
            })
            ->sortByDesc(fn ($row) => [$row->responsible_leads, $row->contacted_leads, $row->total_calls])
            ->values();
    }
}
