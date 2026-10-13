<?php

namespace App\Livewire\Group;

use App\Models\Payment;
use App\Models\Settings;
use App\Models\Transaction;
use Illuminate\Support\Carbon;
use Livewire\Component;
use Livewire\WithPagination;

class TransactionsTable extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $group;
    public $date;

    public function mount($group): void
    {
        $this->date = Carbon::now()->subDays(29)->format('Y.m.d') . " - " . Carbon::now()->format('Y.m.d');
        $this->group = $group;
    }

    public function render()
    {
        $dates = explode(" - ", $this->date);
        $startDate = Carbon::parse(str_replace('.', '-', $dates[0]))->format('Y-m-d');
        $endDate = Carbon::parse(str_replace('.', '-', $dates[1] ?? $dates[0]))->format('Y-m-d');
        $transactions = Transaction::where(function($query) {
                $query->where('from_account_id', $this->group->account->id)
                    ->orWhere('to_account_id', $this->group->account->id);
            })
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->latest()
            ->paginate(20);
        return view('livewire.group.transactions-table', compact('transactions'));
    }
}
