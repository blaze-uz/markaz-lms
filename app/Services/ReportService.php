<?php

namespace App\Services;

use App\Enums\AccountEnum;
use App\Models\Account;
use App\Models\Expense;
use App\Models\Group;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ReportService
{
    public function getChartData($data): array
    {
        $startDate = Carbon::parse($data['from']);
        $endDate = Carbon::parse($data['to']);
        $chartData = [
            'income' => [],
            'expense' => [],
        ];

        // Aggregate income by date
        $payments = Payment::with(['student' => function ($query) {
            $query->withTrashed();
        }])
            ->whereBetween('date', [$startDate, $endDate])
            ->doesntHave('refunds')
            ->groupBy('date')
            ->selectRaw('date, SUM(amount) as total_amount')
            ->get();

        // Aggregate expenses by date
        $expenses = Expense::whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('created_at')
            ->selectRaw('DATE(created_at) as date, SUM(price) as total_price')
            ->get();

        // Initialize arrays to hold daily totals
        $dailyIncome = [];
        $dailyExpense = [];

        // Populate daily income
        foreach ($payments as $payment) {
            $date = Carbon::parse($payment->date)->toDateString();
            $dailyIncome[$date] = $payment->total_amount;
        }

        // Populate daily expenses
        foreach ($expenses as $expense) {
            $date = Carbon::parse($expense->date)->toDateString();
            $dailyExpense[$date] = $expense->total_price;
        }

        // Fill in chart data for each day in the date range
        $currentDate = $startDate->copy();
        while ($currentDate->lte($endDate)) {
            $formattedDate = $currentDate->toDateString();
            $chartData['income'][$formattedDate] = $dailyIncome[$formattedDate] ?? 0;
            $chartData['expense'][$formattedDate] = $dailyExpense[$formattedDate] ?? 0;
            $currentDate->addDay(); // Move to the next day
        }

        return $chartData;
    }

    public function getCardsData(array $data): array
    {
        $startDate = $data['from'];
        $endDate = $data['to'];

        $payments = Payment::whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate)
            ->doesntHave('refunds')
            ->select(DB::raw('SUM(amount) as total_amount'))
            ->first()
            ->total_amount ?? 0;

        //All groups balance
        $groupsBalance = $this->getGroupsBalance();

        //All teachers balance
        $teachersBalance = $this->getStaffBalance();

        //Main account balance
        $currentBranch = auth()->user()->branches()
            ->where('branch_id', auth()->user()->current_branch_id)
            ->first();

        $mainAccountBalance = 0;
        if ($currentBranch && $currentBranch->account_id) {
            $mainAccountBalance = Account::where('id', $currentBranch->account_id)
                ->where('status', 'active')
                ->where('account_code', AccountEnum::MAIN->value)
                ->sum('balance');
        } else {
            // Fallback to all main accounts if no specific branch account
            $mainAccountBalance = Account::where('account_code', AccountEnum::MAIN->value)
                ->where('status', 'active')
                ->sum('balance');
        }

        // Calculate expenses for the date range
        $expenses = Expense::whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate)
            ->sum('price');

        // Calculate staff payments (salary) for the date range
        $staffPayments = $this->getStaffPayments($startDate, $endDate);

        // Calculate total outgoing (expenses + salary)
        $totalOutgoing = $expenses + $staffPayments;

        // Calculate cash balance (income - outgoing)
        $cashBalance = $payments - $totalOutgoing;

        return [
            'payments' => $payments,
            'expenses' => $expenses,
            'staffPayments' => $staffPayments,
            'totalOutgoing' => $totalOutgoing,
            'cashBalance' => $cashBalance,
            'groupsBalance' => $groupsBalance,
            'teachersBalance' => $teachersBalance,
            'mainAccountBalance' => $mainAccountBalance,
        ];
    }

    private function getGroupsBalance()
    {
        $currentBranchId = auth()->user()->current_branch_id;

        // Get account IDs of groups that belong to the current branch
        $groupAccountIds = Group::whereHas('branches', function ($query) use ($currentBranchId) {
            $query->where('branch_id', $currentBranchId);
        })->pluck('account_id')->filter();

        return Account::where('account_code', AccountEnum::GROUP->value)
            ->whereIn('id', $groupAccountIds)
            ->where('status', 'active')
            ->sum('balance');
    }


    private function getStaffBalance()
    {
        $currentBranchId = auth()->user()->current_branch_id;

        // Get account IDs of users that belong to the current branch
        $userAccountIds = User::whereHas('branches', function ($query) use ($currentBranchId) {
            $query->where('branch_id', $currentBranchId);
        })->pluck('account_id')->filter();

        return Account::where('account_code', AccountEnum::STAFF->value)
            ->whereIn('id', $userAccountIds)
            ->where('status', 'active')
            ->sum('balance');
    }

    private function getStaffPayments($startDate, $endDate)
    {
        return DB::table('staff_payments')
            ->join('staff_payment_transaction', 'staff_payments.id', '=', 'staff_payment_transaction.staff_payment_id')
            ->whereDate('staff_payments.created_at', '>=', $startDate)
            ->whereDate('staff_payments.created_at', '<=', $endDate)
            ->where('staff_payments.payment_type', 'withdraw')
            ->sum('staff_payments.amount') ?? 0;
    }

    public function getPaymentTypes(array $data)
    {
        $startDate = $data['from'];
        $endDate = $data['to'];

        $paymentQuery = Payment::query();

        if ($data['branch_id']) {
            $paymentQuery->where('branch_id', $data['branch_id']);
        }

        return $paymentQuery->select('payment_types.name', 'amount')
            ->with('payment_type')
            ->join('payment_types', 'payments.payment_type_id', '=', 'payment_types.id')
            ->whereDate('payments.date', '>=', $startDate)
            ->whereDate('payments.date', '<=', $endDate)
            ->doesntHave('refunds')
            ->get()
            ->groupBy('name')
            ->map(function ($item) {
                return $item->sum('amount');
            });
    }

    public function getPaymentsByCreators(array $data)
    {
        $startDate = $data['from'];
        $endDate = $data['to'];

        return User::whereHas('payments', function ($query) use ($startDate, $endDate) {
            $query->whereDate('date', '>=', $startDate)
                ->whereDate('date', '<=', $endDate)
                ->doesntHave('refunds');
        })->withSum(['payments' => function ($query) use ($startDate, $endDate) {
            $query->whereDate('date', '>=', $startDate)
                ->whereDate('date', '<=', $endDate)
                ->doesntHave('refunds');
        }], 'amount')
            ->get();
    }

    public function getRequestParams($data)
    {
        if (!isset($data['from']) && !isset($data['to'])) {
            $data['from'] = now()->subDays(30);
            $data['to'] = now();
        } else {
            $data['from'] = Carbon::parse(str_replace('.', '-', $data['from']))->format('Y-m-d');
            $data['to'] = Carbon::parse(str_replace('.', '-', $data['to']))->format('Y-m-d');
        }
        return $data;
    }
}
