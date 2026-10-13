<?php

namespace App\Livewire\User;

use App\Models\Transaction;
use Illuminate\Support\Carbon;
use Livewire\Component;
use Livewire\WithPagination;

class PaymentsList extends Component
{
    use WithPagination;

    public $paginationTheme = 'bootstrap';
    public $filter = 'all';
    public $date;

    public function mount()
    {
        $this->date = Carbon::now()->subDays(29)->format('Y.m.d') . " - " . Carbon::now()->format('Y.m.d');
    }

    public function render()
    {
        $dates = explode(" - ", $this->date);
        $startDate = Carbon::parse(str_replace('.', '-', $dates[0]))->format('Y-m-d');
        $endDate = Carbon::parse(str_replace('.', '-', $dates[1] ?? $dates[0]))->format('Y-m-d');

        $account = auth()->user()->account;
        $transactions = Transaction::with(['to_account', 'from_account'])
            ->where(function ($query) use ($startDate, $endDate, $account) {
                $query->whereDate('created_at', '>=', $startDate)
                    ->whereDate('created_at', '<=', $endDate);

                if ($this->filter == 'income') {
                    $query->where('to_account_id', $account->id);
                } elseif ($this->filter == 'expense') {
                    $query->where('from_account_id', $account->id);
                } else {
                    $query->where(function ($query) use ($account) {
                        $query->where('from_account_id', $account->id)
                            ->orWhere('to_account_id', $account->id);
                    });
                }
            })
            ->latest()
            ->paginate(30);

        $income = Transaction::where('to_account_id', $account->id)
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->sum('amount');

        $expense = Transaction::where('from_account_id', $account->id)
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->sum('amount');

        $balance = auth()->user()->account->getBalance();

        return view('livewire.user.payments-list', compact(['transactions', 'income', 'expense', 'balance']));
    }
}
