<?php

namespace App\Livewire\Account;

use App\Enums\AccountEnum;
use App\Models\Account;
use Livewire\Component;
use Livewire\WithPagination;

class OtherAccountsTable extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public function placeholder()
    {
        return view('livewire.placeholders.skeleton');
    }

    public function render()
    {
        $otherAccounts = Account::where('account_code', AccountEnum::MAIN->value)->orderBy('name')->paginate(20);
        return view('livewire.account.other-accounts-table', compact('otherAccounts'));
    }
}
