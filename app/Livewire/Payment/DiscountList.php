<?php

namespace App\Livewire\Payment;

use App\Models\PaymentDiscount;
use Illuminate\Support\Carbon;
use Livewire\Component;
use Livewire\WithPagination;

class DiscountList extends Component
{
    use WithPagination;

    public $paginationTheme = 'bootstrap';
    public $search = '';
    public string $date;

    public function mount()
    {
        $this->date = Carbon::now()->subDays(29)->format('Y.m.d') . " - " . Carbon::now()->format('Y.m.d');
    }

    public function render()
    {
        $dates = explode(" - ", $this->date);
        $startDate = Carbon::parse(str_replace('.', '-', $dates[0]))->format('Y-m-d');
        $endDate = Carbon::parse(str_replace('.', '-', $dates[1] ?? $dates[0]))->format('Y-m-d');
        $discounts = PaymentDiscount::with(['transaction'])
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->orderByDesc('created_at')
            ->paginate(20);

        $discountsSum = PaymentDiscount::whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->get('amount');
        return view('livewire.payment.discount-list', compact('discounts', 'discountsSum'));
    }
}
