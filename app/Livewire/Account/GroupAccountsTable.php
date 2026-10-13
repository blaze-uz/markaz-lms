<?php

namespace App\Livewire\Account;

use App\Enums\AccountEnum;
use App\Models\Account;
use Livewire\Component;
use Livewire\WithPagination;

class GroupAccountsTable extends Component
{
    use WithPagination;

    public $search = '';
    public $groupStatus = 'all';
    protected $paginationTheme = 'bootstrap';

    public function placeholder()
    {
        return view('livewire.placeholders.skeleton');
    }

    public function render()
    {
        $groupAccounts = Account::search($this->search)
            ->where('account_code', AccountEnum::GROUP->value)
            ->when($this->groupStatus !== 'all', function ($query) {
                return $query->where('status', $this->groupStatus);
            })
            ->latest()
            ->paginate(10);
        return view('livewire.account.group-accounts-table', compact('groupAccounts'));
    }
}
