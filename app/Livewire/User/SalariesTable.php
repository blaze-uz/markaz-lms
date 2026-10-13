<?php

namespace App\Livewire\User;

use App\Exports\StaffSalaryExport;
use App\Models\StaffSalary;
use App\Models\User;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class SalariesTable extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $filters = [
        'staff' => '',
        'month' => '',
    ];

    #[On('select2-changed')]
    public function changeFilter($filter, $value)
    {
        $this->filters[$filter] = $value;
    }

    public function exportExcel()
    {
        return Excel::download(new StaffSalaryExport($this->filters), 'staff-salaries.xlsx');
    }

    public function render()
    {
        $staff = User::whereNotIn('id', [auth()->id()])
            ->whereHas('branches', function ($query) {
                $query->where('branch_id', auth()->user()->current_branch_id);
            })
            ->select('id', 'name')
            ->get();

        $salaries = StaffSalary::when($this->filters['staff'], function ($query) {
            $query->where('staff_id', $this->filters['staff']);
        })
            ->when($this->filters['month'], function ($query) {
                $query->whereMonth('date', $this->filters['month']);
            })
            ->whereRelation('staff', function ($query) {
                $query->whereHas('branches', function ($query) {
                    $query->where('branch_id', auth()->user()->current_branch_id);
                });
            })
            ->withCount('transactions')
            ->with('staff')
            ->latest('id')
            ->paginate(10);

        $salaries->getCollection()->transform(function ($salary) {
            $salary->amount = $salary->transactions_count > 0 ? $salary->amount : 0;
            $salary->given_amount = $salary->transactions_count > 0 ? $salary->given_amount : 0;
            return $salary;
        });

        return view('livewire.user.salaries-table', [
            'items' => $salaries,
            'staff' => $staff,
        ]);
    }
}
