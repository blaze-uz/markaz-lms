<?php

namespace App\Livewire\User;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Carbon;
use Livewire\Component;
use Livewire\WithPagination;

class PaymentTable extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $user;
    public $date;

    public function mount($user): void
    {
        $this->date = Carbon::now()->subDays(29)->format('Y.m.d') . " - " . Carbon::now()->format('Y.m.d');
        $this->user = User::with('account')->where('id', $user->id)->first();
    }

    public function render()
    {
        $dates = explode(" - ", $this->date);
        $startDate = Carbon::parse(str_replace('.', '-', $dates[0]))->format('Y-m-d');
        $endDate = Carbon::parse(str_replace('.', '-', $dates[1] ?? $dates[0]))->format('Y-m-d');
        $account = $this->user->account;
        $transactions = Transaction::with('from_account', 'to_account')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('from_account_id', $account->id)
            ->orWhere('to_account_id', $account->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('livewire.user.payment-table', compact('transactions'));
    }
}
