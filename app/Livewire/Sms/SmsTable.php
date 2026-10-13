<?php

namespace App\Livewire\Sms;

use App\Exports\SmsExport;
use App\Models\Sms;
use Illuminate\Support\Carbon;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class SmsTable extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $filterByStatus = 'all';

    public string $date;

    public $perPage = 10;

    public array $selected = [];

    public bool $selectPage = false;

    public bool $selectAll = false;

    public $selectedUsers = [];

    public $selectedStatus = '';

    public $search = '';

    public $showFilters = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'selectedStatus' => ['except' => ''],
        'date' => ['except' => ''],
        'perPage' => ['except' => 10],
        'showFilters' => ['as' => 'filters', 'except' => false],
    ];

    public function mount()
    {
        $today = now()->format('Y.m.d');
        $this->date = "{$today} - {$today}";

        // Restore filter state from query parameters
        $this->selectedStatus = request()->query('selectedStatus', '');
        $this->search = request()->query('search', '');
        $this->showFilters = filter_var(request()->query('filters', false), FILTER_VALIDATE_BOOLEAN);
    }

    public function updatedSelectPage($value): void
    {
        if ($value) {
            [$startDate, $endDate] = $this->getDateRange($this->date);

            $this->selected = $this->builder()
                ->where('status', '!=', 'scheduled')
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
                ->orderByDesc('id')
                ->paginate($this->perPage)
                ->pluck('id')
                ->toArray();
        } else {
            $this->selected = [];
            $this->selectAll = false;
        }
    }

    public function selectAllAcrossPages(): void
    {
        $this->selectAll = true;
    }

    public function clearSelection(): void
    {
        $this->selected = [];
        $this->selectPage = false;
        $this->selectAll = false;
    }

    public function getTotalFilteredCount(): int
    {
        [$startDate, $endDate] = $this->getDateRange($this->date);

        return $this->builder()
            ->where('status', '!=', 'scheduled')
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->count();
    }

    private function getSelectedQuery()
    {
        if ($this->selectAll) {
            [$startDate, $endDate] = $this->getDateRange($this->date);

            return $this->builder()
                ->where('status', '!=', 'scheduled')
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate);
        }

        return Sms::whereIn('id', $this->selected);
    }

    public function updatedSelectedStatus()
    {
        $this->resetPage();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedDate()
    {
        $this->dispatch('chartUpdated', chartData: $this->chartPayload());
    }

    public function getStatusOptions()
    {
        return [
            'pending' => __('translation.pending'),
            'sent' => __('translation.sent'),
            'failed' => __('translation.failed'),
            'waiting' => __('translation.waiting'),
        ];
    }

    public function builder()
    {
        return Sms::query()
            ->when($this->selectedStatus, function ($query) {
                $query->where('status', $this->selectedStatus);
            })
            ->when($this->search, function ($query) {
                $query->where(function ($query) {
                    $query->where('phone', 'ilike', '%'.$this->search.'%')
                        ->orWhere('content', 'ilike', '%'.$this->search.'%');
                });
            });
    }

    private function getChartData(): array
    {
        [$startDate, $endDate] = $this->getDateRange($this->date);

        return $this->builder()
            ->where('status', '!=', 'scheduled')
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as sms_count, COALESCE(SUM(total_price), 0) as total_price')
            ->groupByRaw('DATE(created_at)')
            ->orderBy('date')
            ->get()
            ->toArray();
    }

    public function chartPayload(): array
    {
        $chartData = $this->getChartData();

        $labels = [];
        $counts = [];
        $prices = [];

        foreach ($chartData as $item) {
            $labels[] = Carbon::parse($item['date'])->format('d.m.Y');
            $counts[] = $item['sms_count'];
            $prices[] = (float) $item['total_price'];
        }

        return [
            'labels' => $labels,
            'counts' => $counts,
            'prices' => $prices,
        ];
    }

    public function render()
    {
        [$startDate, $endDate] = $this->getDateRange($this->date);

        $sms = $this->builder()
            ->with('smsable')
            ->where('status', '!=', 'scheduled')
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->orderByDesc('id')
            ->paginate($this->perPage);

        $card = [
            'sent_today' => $this->builder()->whereDate('created_at', today())->where('status', 'sent')->count(),
            'pending' => $this->builder()
                ->whereIn('status', ['pending', 'waiting'])
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
                ->count(),
            'failed' => $this->builder()
                ->where('status', 'failed')
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
                ->count(),
        ];

        $totalSent = $this->builder()
            ->where('status', 'sent')
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->count();

        $totalSms = $this->builder()
            ->whereIn('status', ['sent', 'failed'])
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->count();

        $card['success_rate'] = $totalSms > 0 ? round(($totalSent / $totalSms) * 100) : 0;

        $card['total_cost'] = $this->builder()
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->whereNotNull('total_price')
            ->sum('total_price') ?? 0;

        return view('livewire.sms.sms-table', [
            'sms' => $sms,
            'card' => $card,
            'chartData' => $this->chartPayload(),
        ]);
    }

    private function getCardsData($startDate, $endDate): array
    {
        $today = now()->startOfDay();
        $todayEnd = now()->endOfDay();

        $sentToday = Sms::where('status', 'sent')
            ->whereDate('created_at', today())
            ->count();

        $pending = Sms::where('status', 'pending')
            ->orWhere('status', 'waiting')
            ->count();

        $failed = Sms::where('status', 'failed')
            ->count();

        $totalSent = Sms::where('status', 'sent')->count();
        $totalSms = Sms::count();
        $successRate = $totalSms > 0 ? round(($totalSent / $totalSms) * 100) : 0;

        return [
            'sent_today' => $sentToday,
            'pending' => $pending,
            'failed' => $failed,
            'success_rate' => $successRate,
        ];
    }

    protected function getDateRange($dateRange): array
    {
        if (empty($dateRange)) {
            $today = now()->format('Y-m-d');

            return [$today, $today];
        }

        $dates = explode(' - ', $dateRange);
        if (count($dates) !== 2) {
            $today = now()->format('Y-m-d');

            return [$today, $today];
        }

        $startDate = Carbon::createFromFormat('Y.m.d', $dates[0])->format('Y-m-d');
        $endDate = Carbon::createFromFormat('Y.m.d', $dates[1])->format('Y-m-d');

        return [$startDate, $endDate];
    }

    public function toggleFilters()
    {
        $this->showFilters = ! $this->showFilters;
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->selectedStatus = '';
        $this->resetPage();
    }

    /**
     * Export SMS data to Excel
     */
    public function export(): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        [$startDate, $endDate] = $this->getDateRange($this->date);

        $query = $this->builder()
            ->where('status', '!=', 'scheduled')
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->orderByDesc('id');

        $fileName = 'sms_'.now()->format('Y-m-d_His').'.xlsx';

        return Excel::download(new SmsExport($query), $fileName);
    }
}
