<?php

namespace App\Http\Controllers;

use App\DTOs\Account\AccountDTO;
use App\DTOs\Account\AccountFilterDTO;
use App\Enums\AccountEnum;
use App\Http\Requests\Account\AccountRequest;
use App\Services\AccountService;
use Illuminate\Http\Request;

class AccountController extends BaseController
{
    protected string $view_folder = 'admin.accounts';
    protected string $index_route = 'admin.accounts.index';
    protected string $create_route = 'admin.accounts.create';
    protected string $edit_route = 'admin.accounts.edit';

    protected $dto = AccountDTO::class;

    protected $filterDto = AccountFilterDTO::class;

    protected $requestClass = AccountRequest::class;

    protected $serviceClass = AccountService::class;

    public function index(Request $request): \Illuminate\Contracts\View\View
    {
        $staffAccountsSum = $this->service->getAccountsSum(AccountEnum::STAFF->value);
        $studentAccountsSum = $this->service->getAccountsSum(AccountEnum::STUDENT->value);
        $groupAccountsSum = $this->service->getAccountsSum(AccountEnum::GROUP->value);
        $otherAccountsSum = $this->service->getAccountsSum(AccountEnum::MAIN->value);

        return view('admin.accounts.index', [
            'staffAccountsSum' => $staffAccountsSum,
            'studentAccountsSum' => $studentAccountsSum,
            'groupAccountsSum' => $groupAccountsSum,
            'otherAccountsSum' => $otherAccountsSum,
        ]);
    }

    public function show($id)
    {
        $item = $this->service->find($id);
        $transactions = $this->service->getTransactions($id);
        return view("admin.accounts.show", compact('item', 'transactions'));
    }

    public function transfer()
    {
        return view('admin.accounts.transfer');
    }

    public function transferStore(Request $request)
    {
        $data = $request->validate([
            'from_account_id' => 'required',
            'to_account_id' => 'required',
            'amount' => 'required',
        ]);
        $this->service->transfer($data);
        session()->flash("success_alert", __("translation.alert_messages.transfer_success"));
        return redirect()->route('admin.accounts.index');
    }

    public function transactionView($id)
    {
        $item = $this->service->find($id);
        $accounts = $this->service->getStaffAccounts();
        return view("admin.accounts.transaction", [
            'item' => $item,
            'accounts' => $accounts
        ]);
    }

    public function transaction(Request $request, $id)
    {
        $data = $request->validate([
            'amount' => 'required',
            'type' => 'required',
            'description' => 'sometimes'
        ]);

        $this->service->transaction($data, $id);
        session()->flash("success_alert", __("translation.alert_messages.transaction_success"));
        return redirect()->route('admin.accounts.show', $id);
    }
}
