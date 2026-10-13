<?php

namespace App\Livewire\Report;

use App\Models\PaymentDiscount;
use App\Models\RefundPayment;
use Livewire\Component;

class ExpensesCard extends Component
{
    public $start;
    public $end;

    public function render()
    {
        if (!$this->start || !$this->end) {
            $this->start = now()->subDays(30);
            $this->end = now();
        }
        //get expenses count and total price
        $expenses = \App\Models\Expense::selectRaw('count(*) as count, sum(price) as total_amount')
            ->whereDate('created_at', '>=', $this->start)
            ->whereDate('created_at', '<=', $this->end)
            ->first();

        $refunds = RefundPayment::selectRaw('count(*) as count, sum(amount) as total_amount')
            ->whereDate('created_at', '>=', $this->start)
            ->whereDate('created_at', '<=', $this->end)
            ->first();

        $discounts = PaymentDiscount::selectRaw('count(*) as count, sum(amount) as total_amount')
            ->whereDate('created_at', '>=', $this->start)
            ->whereDate('created_at', '<=', $this->end)
            ->first();

        return view('livewire.report.expenses-card', compact('expenses', 'refunds', 'discounts'));
    }
}
