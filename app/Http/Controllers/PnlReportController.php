<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Expense;
use App\Models\PaymentType;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PnlReportController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->get('period', 'monthly');
        $date = $request->get('date', now()->format('Y-m'));

        $startDate = Carbon::parse($date)->startOf($period == 'monthly' ? 'month' : 'year');
        $endDate = Carbon::parse($date)->endOf($period == 'monthly' ? 'month' : 'year');

        // Get total income
        $totalIncome = Payment::whereBetween('created_at', [$startDate, $endDate])
            ->sum('amount') ?? 0;

        // Get total expenses - multiply price by quantity
        $totalExpenses = Expense::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('SUM(price * quantity) as total_amount')
            ->value('total_amount') ?? 0;

        // Calculate net profit
        $netProfit = $totalIncome - $totalExpenses;

        // Get income by category with proper casting
        $incomeByCategory = Payment::whereBetween('payments.created_at', [$startDate, $endDate])
            ->join('payment_types', 'payments.payment_type_id', '=', 'payment_types.id')
            ->selectRaw('payment_types.name as category, CAST(SUM(payments.amount) AS DECIMAL(10,2)) as total')
            ->groupBy('payment_types.id', 'payment_types.name')
            ->get();

        // Get expenses by category with proper casting
        $expensesByCategory = Expense::whereBetween('expenses.created_at', [$startDate, $endDate])
            ->join('expense_items', 'expenses.item_id', '=', 'expense_items.id')
            ->selectRaw('expense_items.name as category, CAST(SUM(expenses.price * expenses.quantity) AS DECIMAL(10,2)) as total')
            ->groupBy('expense_items.id', 'expense_items.name')
            ->get();

        // Ensure we have at least one category for each chart to prevent empty chart errors
        if ($incomeByCategory->isEmpty()) {
            $incomeByCategory->push((object)[
                'category' => 'No Income',
                'total' => 0
            ]);
        }

        if ($expensesByCategory->isEmpty()) {
            $expensesByCategory->push((object)[
                'category' => 'No Expenses',
                'total' => 0
            ]);
        }

        // Fill in missing dates with zero values
        $dates = collect();
        $currentDate = clone $startDate;
        while ($currentDate <= $endDate) {
            $dates->push($currentDate->format('Y-m-d'));
            $currentDate->addDay();
        }

        // Get daily income with proper casting
        $dailyIncome = Payment::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as date, CAST(SUM(amount) AS DECIMAL(10,2)) as total')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();

        $dailyIncomeMap = $dailyIncome->pluck('total', 'date')->all();

        // Prepare chart data
        $chartData = [
            'income' => [
                'labels' => $incomeByCategory->pluck('category')->values()->all(),
                'data' => $incomeByCategory->pluck('total')->map(function($value) {
                    return (float) $value;
                })->values()->all(),
            ],
            'expense' => [
                'labels' => $expensesByCategory->pluck('category')->values()->all(),
                'data' => $expensesByCategory->pluck('total')->map(function($value) {
                    return (float) $value;
                })->values()->all(),
            ],
            'trend' => [
                'labels' => $dates->values()->all(),
                'data' => $dates->map(function($date) use ($dailyIncomeMap) {
                    return (float) ($dailyIncomeMap[$date] ?? 0);
                })->values()->all(),
            ]
        ];

        // Log the data for debugging
        \Log::info('Chart Data:', $chartData);

        return view('reports.pnl', [
            'totalIncome' => $totalIncome,
            'totalExpenses' => $totalExpenses,
            'netProfit' => $netProfit,
            'period' => $period,
            'date' => $date,
            'incomeChartData' => $chartData['income'],
            'expenseChartData' => $chartData['expense'],
            'trendChartData' => $chartData['trend']
        ]);
    }
}
