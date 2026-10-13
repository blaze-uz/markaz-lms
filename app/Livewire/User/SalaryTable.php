<?php

namespace App\Livewire\User;

use App\Models\StaffSalary;
use Livewire\Component;

class SalaryTable extends Component
{
    public $user;

    public function mount($user)
    {
        $this->user = $user;
    }

    public function render()
    {
        $salaries = StaffSalary::with(['transactions'])->where('staff_id', $this->user->id)->latest()->get();
        $salaries->transform(function ($salary) {
            $salary->amount = $salary->transactions->where('pivot.type', 'income')->sum('amount');
            $salary->given_amount = $salary->transactions->where('pivot.type', 'expense')->sum('amount');
            return $salary;
        });
        return view('livewire.user.salary-table', compact('salaries'));
    }
}
