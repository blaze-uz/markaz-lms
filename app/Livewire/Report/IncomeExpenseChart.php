<?php

namespace App\Livewire\Report;

use App\Models\Expense;
use App\Models\Payment;
use Illuminate\Support\Carbon;
use Livewire\Component;

class IncomeExpenseChart extends Component
{
    public $date;
    public $chartData;

    public function mount(): void
    {
        $this->date = Carbon::now()->subDays(29)->format('Y.m.d') . " - " . Carbon::now()->format('Y.m.d');
        $this->getChartData();
    }

    public function render()
    {
        return view('livewire.report.income-expense-chart');
    }

    public function getChartData(): void
    {
        $dates = explode(" - ", $this->date);
        $startDate = Carbon::parse(str_replace('.', '-', $dates[0]))->format('Y-m-d');
        $endDate = Carbon::parse(str_replace('.', '-', $dates[1] ?? $dates[0]))->format('Y-m-d');

        $this->chartData = [
            'income' => [],
            'expense' => [],
        ];

        // Iterate through each day in the date range
        $currentDate = Carbon::parse($startDate);
        while ($currentDate->lte($endDate)) {
            $formattedDate = $currentDate->toDateString();

            // Query the Payment model for income on the current date
            $dailyIncome = Payment::whereDate('created_at', $formattedDate)->sum('amount');

            // Query the Expense model for expenses on the current date
            $dailyExpense = Expense::whereDate('created_at', $formattedDate)->sum('price');

            // Store the data for the current date in the chartData array
            $this->chartData['income'][$formattedDate] = $dailyIncome;
            $this->chartData['expense'][$formattedDate] = $dailyExpense;

            $currentDate->addDay(); // Move to the next day
        }

        $this->dispatch('incomeExpenseChartChanged');
    }
}
