<?php

namespace App\Livewire\Report\Sms;

use App\Models\Sms;
use App\Traits\HasDateRange;
use Livewire\Component;

class DailySpending extends Component
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
        return <<<'HTML'
        <div class="card rounded-4 card-height-100">
            <div class="card-header rounded-top-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="placeholder-glow">
                        <span class="placeholder col-4"></span>
                    </div>
                    <div class="placeholder-glow">
                        <span class="placeholder col-6"></span>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="text-center">
                            <div class="placeholder-glow">
                                <span class="placeholder col-8 mb-2"></span>
                                <span class="placeholder col-6"></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center">
                            <div class="placeholder-glow">
                                <span class="placeholder col-8 mb-2"></span>
                                <span class="placeholder col-6"></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center">
                            <div class="placeholder-glow">
                                <span class="placeholder col-8 mb-2"></span>
                                <span class="placeholder col-6"></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th><span class="placeholder col-6"></span></th>
                                <th class="text-end"><span class="placeholder col-4"></span></th>
                                <th class="text-end"><span class="placeholder col-4"></span></th>
                                <th class="text-end"><span class="placeholder col-4"></span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @for ($i = 0; $i < 5; $i++)
                                <tr>
                                    <td>
                                        <div class="placeholder-glow">
                                            <span class="placeholder col-8 mb-1"></span>
                                            <span class="placeholder col-6"></span>
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <span class="placeholder col-3"></span>
                                    </td>
                                    <td class="text-end">
                                        <span class="placeholder col-4"></span>
                                    </td>
                                    <td class="text-end">
                                        <span class="placeholder col-4"></span>
                                    </td>
                                </tr>
                            @endfor
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        HTML;
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

        // Get daily spending data
        $dailySpending = $query->sent()
            ->selectRaw('DATE(created_at) as date, SUM(total_price) as total_spent, COUNT(*) as sms_count')
            ->whereNotNull('total_price')
            ->where('total_price', '>', 0)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Calculate total spending
        $totalSpent = $dailySpending->sum('total_spent');
        $totalSms = $dailySpending->sum('sms_count');
        $averagePerDay = $dailySpending->count() > 0 ? round($totalSpent / $dailySpending->count()) : 0;

        return view('livewire.report.sms.daily-spending', [
            'dailySpending' => $dailySpending,
            'totalSpent' => $totalSpent,
            'totalSms' => $totalSms,
            'averagePerDay' => $averagePerDay,
        ]);
    }
}
