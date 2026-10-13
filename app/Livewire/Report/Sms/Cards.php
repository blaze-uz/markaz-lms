<?php

namespace App\Livewire\Report\Sms;

use App\Models\Sms;
use App\Traits\HasDateRange;
use Livewire\Component;

class Cards extends Component
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

    public function placeholder()
    {
        return view('livewire.placeholders.skeleton');
    }

    public function render()
    {
        $query = Sms::query();

        if ($this->startDate) {
            $query->whereDate('created_at', '>=', $this->startDate);
        }
        if ($this->endDate) {
            $query->whereDate('created_at', '<=', $this->endDate);
        }

        // Basic SMS counts
        $totalSms = $query->count();
        $sentSms = (clone $query)->sent()->count();
        $failedSms = (clone $query)->failed()->count();
        $pendingSms = (clone $query)->pending()->count();
        $scheduledSms = (clone $query)->whereNotNull('scheduled_at')->count();

        // Additional metrics
        $successRate = $totalSms > 0 ? round(($sentSms / $totalSms) * 100, 1) : 0;
        $failedRate = $totalSms > 0 ? round(($failedSms / $totalSms) * 100, 1) : 0;

        // Calculate actual cost from sent SMS during the period
        $actualCost = $query->where('status', 'sent')->whereNotNull('total_price')->sum('total_price') ?? 0;

        // Additional metrics
        $averageCostPerSms = $sentSms > 0 ? round($actualCost / $sentSms) : 0;

        // Calculate daily average SMS
        $daysDiff = 1;
        if ($this->startDate && $this->endDate) {
            $start = \Carbon\Carbon::parse($this->startDate);
            $end = \Carbon\Carbon::parse($this->endDate);
            $daysDiff = max(1, $start->diffInDays($end) + 1);
        }
        $dailyAverage = round($totalSms / $daysDiff);

        // Calculate efficiency score (combination of success rate and cost efficiency)
        $efficiencyScore = 0;
        if ($totalSms > 0) {
            if ($averageCostPerSms > 0) {
                $costEfficiency = min(100, (10000 / $averageCostPerSms)); // Lower cost = higher efficiency
            } else {
                $costEfficiency = 100; // If no cost, consider it 100% efficient
            }
            $efficiencyScore = round(($successRate + $costEfficiency) / 2);
        }

        return view('livewire.report.sms.cards', [
            'totalSms' => $totalSms,
            'sentSms' => $sentSms,
            'failedSms' => $failedSms,
            'pendingSms' => $pendingSms,
            'scheduledSms' => $scheduledSms,
            'successRate' => $successRate,
            'failedRate' => $failedRate,
            'actualCost' => $actualCost,
            'averageCostPerSms' => $averageCostPerSms,
            'dailyAverage' => $dailyAverage,
            'efficiencyScore' => $efficiencyScore,
        ]);
    }
}
