<?php

namespace App\Livewire\Account;

use App\Enums\AccountEnum;
use App\Models\Account;
use Livewire\Component;
use Livewire\WithPagination;

class StudentAccountsTable extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $search = '';
    public $status = 'all';

    public function placeholder()
    {
        return view('livewire.placeholders.skeleton');
    }

    public function render()
    {
        $studentAccounts = Account::search($this->search)
            ->where('account_code', AccountEnum::STUDENT->value)
            ->when($this->status !== 'all', function ($query) {
                return $query->where('status', $this->status);
            })
            ->latest()
            ->paginate(20);
        return view('livewire.account.student-accounts-table', compact('studentAccounts'));
    }
}
