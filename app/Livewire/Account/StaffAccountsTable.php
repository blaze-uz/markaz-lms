<?php

namespace App\Livewire\Account;

use App\Enums\AccountEnum;
use App\Models\Account;
use Livewire\Component;
use Livewire\WithPagination;

class StaffAccountsTable extends Component
{
    use WithPagination;

    public $search = '';
    public $staffStatus = 'all';
    protected $paginationTheme = 'bootstrap';

    public function placeholder()
    {
        return view('livewire.placeholders.skeleton');
    }

    public function render()
    {
        $staffAccounts = Account::search($this->search)
            ->where('account_code', AccountEnum::STAFF->value)
            ->when($this->staffStatus !== 'all', function ($query) {
                return $query->where('status', $this->staffStatus);
            })
            ->orderBy('name')
            ->paginate(10);
        return view('livewire.account.staff-accounts-table', compact('staffAccounts'));
    }
}
