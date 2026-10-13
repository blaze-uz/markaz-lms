<?php

namespace App\Livewire\Reports;

use App\Models\Expense;
use App\Models\ExpenseItem;
use App\Models\ExpensePlace;
use App\Models\Account;
use App\Models\PaymentType;
use App\Models\User;
use App\Exports\ExpensesExport;
use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ExpensesReport extends Component
{
    use WithPagination;

    // Filter properties
    public $search = '';
    public $selectedItem = '';
    public $selectedPlace = '';
    public $selectedAccount = '';
    public $selectedPaymentType = '';
    public $selectedUser = '';
    public $date = '';

    // Data properties
    public $items = [];
    public $places = [];
    public $accounts = [];
    public $paymentTypes = [];
    public $users = [];

    // Statistics properties
    public $totalExpenses = 0;
    public $totalAmount = 0;
    public $averageAmount = 0;
    public $thisMonthAmount = 0;
    public $lastMonthAmount = 0;
    public $monthlyGrowth = 0;
    public $topExpenseItem = null;
    public $topExpensePlace = null;
    public $recentExpenses = [];

    // Chart data
    public $monthlyData = [];
    public $categoryData = [];
    public $paymentTypeData = [];
    public $monthlyCategories = [];
    public $monthlySeries = [];


    protected $queryString = [
        'search' => ['except' => ''],
        'selectedItem' => ['except' => ''],
        'selectedPlace' => ['except' => ''],
        'selectedAccount' => ['except' => ''],
        'selectedPaymentType' => ['except' => ''],
        'selectedUser' => ['except' => ''],
        'date' => ['except' => ''],
    ];

    public function mount($from = null, $to = null)
    {
        $this->date = Carbon::now()->subWeek()->format('Y.m.d') . " - " . Carbon::now()->format('Y.m.d');
        $this->loadFilters();
        $this->loadStatistics();
        $this->loadChartData();
    }

    public function loadFilters()
    {
        $this->items = ExpenseItem::orderBy('name')->get();
        $this->places = ExpensePlace::orderBy('name')->get();
        $this->accounts = Account::active()->orderBy('name')->get();
        $this->paymentTypes = PaymentType::orderBy('position')->get();
        $this->users = User::whereHas('expenses')->orderBy('name')->get();
    }

    public function getBaseQuery()
    {
        [$startDate, $endDate] = $this->getDateRange();

        return Expense::query()
            ->when($this->search, fn($q) => $q->search($this->search))
            ->when($this->selectedItem, fn($q) => $q->where('item_id', $this->selectedItem))
            ->when($this->selectedPlace, fn($q) => $q->where('place_id', $this->selectedPlace))
            ->when($this->selectedAccount, fn($q) => $q->where('account_id', $this->selectedAccount))
            ->when($this->selectedPaymentType, fn($q) => $q->where('payment_type_id', $this->selectedPaymentType))
            ->when($this->selectedUser, fn($q) => $q->where('created_by', $this->selectedUser))
            ->when($this->date, function($q) use ($startDate, $endDate) {
                $q->whereBetween('date', [$startDate, $endDate]);
            });
    }

    public function loadStatistics()
    {

        $this->totalExpenses = $this->getBaseQuery()->count();


        $this->totalAmount = $this->getBaseQuery()->sum(DB::raw('quantity * price'));


        $this->averageAmount = $this->totalExpenses > 0 ? $this->totalAmount / $this->totalExpenses : 0;


        $thisMonthQuery = $this->getBaseQuery()
            ->whereMonth('date', Carbon::now()->month)
            ->whereYear('date', Carbon::now()->year);
        $this->thisMonthAmount = $thisMonthQuery->sum(DB::raw('quantity * price'));


        $lastMonthQuery = $this->getBaseQuery()
            ->whereMonth('date', Carbon::now()->subMonth()->month)
            ->whereYear('date', Carbon::now()->subMonth()->year);
        $this->lastMonthAmount = $lastMonthQuery->sum(DB::raw('quantity * price'));

        // Monthly growth percentage
        $this->monthlyGrowth = $this->lastMonthAmount > 0
            ? (($this->thisMonthAmount - $this->lastMonthAmount) / $this->lastMonthAmount) * 100
            : 0;

        $top = $this->getBaseQuery()
            ->join('expense_items', 'expenses.item_id', '=', 'expense_items.id')
            ->select(
                'expenses.item_id as item_id',
                'expense_items.name as item_name',
                DB::raw('SUM(expenses.quantity * expenses.price) as total')
            )
            ->groupBy('expenses.item_id', 'expense_items.name')
            ->orderByDesc('total')
            ->first();

        $this->topExpenseItem = $top ? [
            'item_id' => (int) $top->item_id,
            'name'    => $top->item_name,
            'total'   => (float) $top->total,
        ] : null;

        // Top expense place
        $topPlace = $this->getBaseQuery()
            ->join('expense_places', 'expenses.place_id', '=', 'expense_places.id')
            ->select(
                'expenses.place_id as place_id',
                'expense_places.name as place_name',
                DB::raw('SUM(expenses.quantity * expenses.price) as total')
            )
            ->groupBy('expenses.place_id', 'expense_places.name')
            ->orderByDesc('total')
            ->first();

        $this->topExpensePlace = $topPlace ? [
            'place_id' => (int) $topPlace->place_id,
            'name'     => $topPlace->place_name,
            'total'    => (float) $topPlace->total,
        ] : null;
    }

    public function loadChartData()
    {
        // Monthly expenses data - use the same base query as the table
        $monthlyExpenses = $this->getBaseQuery()
            ->select(
                DB::raw('EXTRACT(MONTH FROM date) as month'),
                DB::raw('EXTRACT(YEAR FROM date) as year'),
                DB::raw('SUM(quantity * price) as total')
            )
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        $this->monthlyData = $monthlyExpenses->map(function ($item) {
            return [
                'month' => Carbon::create($item->year, $item->month)->format('M Y'),
                'total' => $item->total
            ];
        });

        // Category (items) breakdown
        $categoryExpenses = $this->getBaseQuery()
            ->join('expense_items', 'expenses.item_id', '=', 'expense_items.id')
            ->select('expense_items.name', DB::raw('SUM(expenses.quantity * expenses.price) as total'))
            ->groupBy('expense_items.name')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get();

        $this->categoryData = $categoryExpenses->map(function ($item) {
            return [
                'name' => $item->name,
                'total' => $item->total
            ];
        });

        // Daily or Monthly expenses by item (for multi-line chart) based on selected range
        [$startDate, $endDate] = $this->getDateRange();
        $diffInDays = $startDate->diffInDays($endDate);
        $aggregateMonthly = $diffInDays >= 90; // switch to monthly when range is large (e.g., ~3+ months)

        if ($aggregateMonthly) {
            // Aggregate by month
            $byItem = $this->getBaseQuery()
                ->join('expense_items', 'expenses.item_id', '=', 'expense_items.id')
                ->select(
                    DB::raw('EXTRACT(MONTH FROM date) as month'),
                    DB::raw('EXTRACT(YEAR FROM date) as year'),
                    'expense_items.id as item_id',
                    'expense_items.name as item_name',
                    DB::raw('SUM(expenses.quantity * expenses.price) as total')
                )
                ->groupBy('year', 'month', 'expense_items.id', 'expense_items.name')
                ->orderBy('year', 'asc')
                ->orderBy('month', 'asc')
                ->get();

            // Build ordered month categories between range
            $months = [];
            $cursor = $startDate->copy()->startOfMonth();
            while ($cursor->lte($endDate)) {
                $months[] = $cursor->format('M Y');
                $cursor->addMonth();
            }
            $this->monthlyCategories = $months;

            // Group by item and map totals per month
            $groupedByItem = $byItem->groupBy('item_name');
            $this->monthlySeries = $groupedByItem->map(function ($rows, $itemName) use ($months) {
                $byMonth = [];
                foreach ($rows as $r) {
                    $key = Carbon::create($r->year, $r->month)->format('M Y');
                    $byMonth[$key] = (float) $r->total;
                }
                $data = collect($months)->map(function ($m) use ($byMonth) {
                    return $byMonth[$m] ?? 0;
                })->all();
                return [
                    'name' => $itemName,
                    'data' => $data,
                ];
            })->values()->all();
        } else {
            // Aggregate by day
            $byItem = $this->getBaseQuery()
                ->join('expense_items', 'expenses.item_id', '=', 'expense_items.id')
                ->select(
                    DB::raw('DATE(date) as day'),
                    'expense_items.id as item_id',
                    'expense_items.name as item_name',
                    DB::raw('SUM(expenses.quantity * expenses.price) as total')
                )
                ->groupBy('day', 'expense_items.id', 'expense_items.name')
                ->orderBy('day', 'asc')
                ->get();

            // Build continuous daily categories between selected range
            $days = [];
            $cursor = $startDate->copy()->startOfDay();
            while ($cursor->lte($endDate)) {
                $days[] = $cursor->format('Y-m-d');
                $cursor->addDay();
            }
            $this->monthlyCategories = $days; // reuse property for categories

            // Group by item and map totals per day
            $groupedByItem = $byItem->groupBy('item_name');
            $this->monthlySeries = $groupedByItem->map(function ($rows, $itemName) use ($days) {
                $byDay = [];
                foreach ($rows as $r) {
                    $byDay[$r->day] = (float) $r->total;
                }
                $data = collect($days)->map(function ($d) use ($byDay) {
                    return $byDay[$d] ?? 0;
                })->all();
                return [
                    'name' => $itemName,
                    'data' => $data,
                ];
            })->values()->all();
        }

        // Payment type breakdown
        $paymentTypeExpenses = $this->getBaseQuery()
            ->join('payment_types', 'expenses.payment_type_id', '=', 'payment_types.id')
            ->select('payment_types.name', DB::raw('SUM(expenses.quantity * expenses.price) as total'))
            ->groupBy('payment_types.name')
            ->orderBy('total', 'desc')
            ->get();

        $this->paymentTypeData = $paymentTypeExpenses->map(function ($item) {
            return [
                'name' => $item->name,
                'total' => $item->total
            ];
        });
    }



    // Removed table pagination/property as UI no longer shows a table

    public function updated($property)
    {
        if (in_array($property, ['search', 'selectedItem', 'selectedPlace', 'selectedAccount', 'selectedPaymentType', 'selectedUser', 'date'])) {
            $this->resetPage();
            $this->loadStatistics();
            $this->loadChartData();
            $this->dispatch('charts-updated');
            $this->js('reinitializeCharts()');

        }
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->selectedItem = '';
        $this->selectedPlace = '';
        $this->selectedAccount = '';
        $this->selectedPaymentType = '';
        $this->selectedUser = '';
        $this->date = Carbon::now()->format('Y.m.d') . " - " . Carbon::now()->format('Y.m.d');
        $this->resetPage();
        $this->loadStatistics();
        $this->loadChartData();
        $this->dispatch('charts-updated');
        $this->js('reinitializeCharts()');
    }

    public function export($format)
    {
        $filename = 'expenses_report_' . now()->format('Y-m-d_H-i-s');

        if ($format === 'xlsx') {
            return Excel::download(
                new ExpensesExport($this->search, $this->date, $this->selectedPaymentType ?: 'all'),
                $filename . '.xlsx'
            );
        } elseif ($format === 'csv') {
            return Excel::download(
                new ExpensesExport($this->search, $this->date, $this->selectedPaymentType ?: 'all'),
                $filename . '.csv'
            );
        }
    }

    public function render()
    {
        return view('livewire.reports.expenses-report');
    }

    private function getDateRange()
    {
        if (!$this->date) {
            $startDate = Carbon::now()->startOfDay();
            $endDate = Carbon::now()->endOfDay();
            return [$startDate, $endDate];
        }

        $dates = explode(' - ', $this->date);
        $startDate = Carbon::parse(str_replace('.', '-', $dates[0]))->startOfDay();
        $endDate = Carbon::parse(str_replace('.', '-', $dates[1] ?? $dates[0]))->endOfDay();

        return [$startDate, $endDate];
    }

    // Date is controlled via the daterangepicker button

}
