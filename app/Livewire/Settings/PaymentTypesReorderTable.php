<?php

namespace App\Livewire\Settings;

use App\Models\PaymentType;
use Livewire\Component;

class PaymentTypesReorderTable extends Component
{
    public function render()
    {
        $paymentTypes = PaymentType::orderBy('position')->get();
        return view('livewire.settings.payment-types-reorder-table', compact('paymentTypes'));
    }
}
