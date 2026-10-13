<?php

namespace App\Livewire\Expense;

use App\Exports\ExpensesExport;
use App\Models\Expense;
use App\Models\ExpenseItem;
use App\Models\ExpensePlace;
use App\Models\PaymentType;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class ExpenseList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $search;
    public $date;
    public $paymentType = 'all', $paymentTypeName = 'all';
    public $creator;

    public function mount(): void
    {
        //set date from request date or default to current month
        if (request()->has('date')) {
            $this->date = request()->input('date');
        } else {
            $this->date = Carbon::now()->format('Y.m.d') . " - " . Carbon::now()->format('Y.m.d');
        }
    }

    public function typeFilter($value): void
    {
        $paymentType = PaymentType::where('name', $value)->first();

        if ($this->paymentType === $paymentType?->id) {
            $this->paymentType = 'all';
            $this->paymentTypeName = 'all';
        } else {
            $this->paymentType = $paymentType?->id;
            $this->paymentTypeName = $paymentType?->name;
        }
        $this->resetPage();
    }

    public function export()
    {
        [$startDate, $endDate] = $this->getDateRange();
        $dateRange = $startDate->format('Y.m.d') . " - " . $endDate->format('Y.m.d');

        return Excel::download(new ExpensesExport(
            $this->search,
            $dateRange,
            $this->paymentType
        ), now()->format('d.m.Y') . '-expenses.xlsx');
    }

    public function render()
    {
        if (!$this->date) {
            $this->date = Carbon::now()->format('Y.m.d') . " - " . Carbon::now()->format('Y.m.d');
        }

        [$startDate, $endDate] = $this->getDateRange();

        $expenseQuery = Expense::search($this->search)
            ->with(['item', 'place', 'account', 'createdBy', 'paymentType'])
            ->whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate)
            ->when($this->paymentType !== 'all', function ($query) {
                $query->where('payment_type_id', $this->paymentType);
            });

        if (request()->creator) {
            $expenseQuery->where('created_by', request()->creator);
        }

        $creators = $expenseQuery->get()->map(function ($expense) {
            return ['id' => $expense->createdBy?->id ?? 0, 'name' => $expense->createdBy?->name ?? 'Unknown'];
        })->unique('id');

        if ($expenseItem = request()->get('expense-item')) {
            $expenseQuery->where('item_id', $expenseItem);
        }

        if ($expensePlace = request()->get('expense-place')) {
            $expenseQuery->where('place_id', $expensePlace);
        }

        $paymentTypeCards = $expenseQuery->get()->groupBy('paymentType.name')
            ->map(function ($group) {
                return $group->sum('price');
            });

        $items = $expenseQuery->orderByDesc('date')->paginate(20);
        $expenseSum = $expenseQuery->sum('price');

        $expenseItems = ExpenseItem::orderBy('name')->get();
        $expensePlaces = ExpensePlace::orderBy('name')->get();


        return view('livewire.expense.expense-list', [
            'items' => $items,
            'expenseSum' => $expenseSum,
            'expenseItems' => $expenseItems,
            'expensePlaces' => $expensePlaces,
            'paymentTypeCards' => $paymentTypeCards,
            'creators' => $creators,
        ]);
    }

    private function getDateRange()
    {
        try {
            $dates = explode(" - ", $this->date);
            if (count($dates) !== 2) {
                // Default to today if invalid format
                return [Carbon::now()->startOfDay(), Carbon::now()->endOfDay()];
            }

            $startDate = Carbon::createFromFormat('Y.m.d', trim($dates[0]))->startOfDay();
            $endDate = Carbon::createFromFormat('Y.m.d', trim($dates[1]))->endOfDay();

            return [$startDate, $endDate];
        } catch (\Exception $e) {
            // Default to today if any parsing error occurs
            return [Carbon::now()->startOfDay(), Carbon::now()->endOfDay()];
        }
    }
}
