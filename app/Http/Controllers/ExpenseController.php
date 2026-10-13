<?php

namespace App\Http\Controllers;

use App\Enums\AccountEnum;
use App\Models\Account;
use App\Models\Expense;
use App\Models\ExpenseItem;
use App\Models\ExpensePlace;
use App\Models\PaymentType;
use App\Models\User;
use App\Services\ExpenseService;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public $service;

    public function __construct()
    {
        $this->middleware('permission:expense-index')->only('index');
        $this->middleware('permission:expense-show')->only('show');
        $this->middleware('permission:expense-create')->only('create', 'store');
        $this->middleware('permission:expense-edit')->only('edit', 'update');
        $this->middleware('permission:expense-delete')->only('destroy');
        $this->service = new ExpenseService();
    }

    public function index()
    {
        return view('admin.expenses.index');
    }

    public function show($id)
    {
        $item = $this->service->show($id);
        $activities = $this->service->getActivities($id);
        return view('admin.expenses.show', [
            'item' => $item,
            'activities' => $activities,
        ]);
    }

    public function create()
    {
        $items = ExpenseItem::all();
        $places = ExpensePlace::all();

        $currentBranch = auth()->user()->currentBranch;
        $currentBranchAccountId = $currentBranch ? $currentBranch->account_id : null;

        // Get STAFF accounts for users in the current branch
        $staffAccountIds = User::whereHas('branches', function ($query) {
            $query->where('branch_id', auth()->user()->current_branch_id);
        })
            ->pluck('account_id')
            ->filter()
            ->toArray();

        $accounts = Account::where('status', 'active')
            ->where(function ($query) use ($currentBranchAccountId, $staffAccountIds) {
                $query->where(function ($subQuery) use ($staffAccountIds) {
                    $subQuery->where('account_code', AccountEnum::STAFF->value)
                        ->whereIn('id', $staffAccountIds);
                })
                    ->orWhere(function ($subQuery) use ($currentBranchAccountId) {
                        $subQuery->where('account_code', AccountEnum::MAIN->value)
                            ->where('id', $currentBranchAccountId);
                    });
            })
            ->orderBy('account_code', 'asc')
            ->get();
        $paymentTypes = PaymentType::all();

        return view('admin.expenses.create', [
            'items' => $items,
            'places' => $places,
            'accounts' => $accounts,
            'paymentTypes' => $paymentTypes
        ]);
    }

    /**
     * @throws \Exception
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'item_id' => 'required',
            'quantity' => 'required',
            'amount' => 'required',
            'place_id' => 'sometimes',
            'account_id' => 'required',
            'payment_type_id' => 'required',
            'date' => 'nullable|date|date_format:Y-m-d',
            'description' => 'sometimes'
        ]);

        $this->service->store($data);
        session()->flash("success_alert", __('translation.alert_messages.created'));
        return redirect()->route('admin.expenses.index');
    }

    public function edit(Expense $expense)
    {
        $items = ExpenseItem::all();
        $places = ExpensePlace::all();

        $currentBranch = auth()->user()->currentBranch;
        $currentBranchAccountId = $currentBranch ? $currentBranch->account_id : null;

        // Get STAFF accounts for users in the current branch
        $staffAccountIds = User::whereHas('branches', function ($query) {
            $query->where('branch_id', auth()->user()->current_branch_id);
        })
            ->pluck('account_id')
            ->filter()
            ->toArray();

        $accounts = Account::where('status', 'active')
            ->where(function ($query) use ($currentBranchAccountId, $staffAccountIds) {
                $query->where(function ($subQuery) use ($staffAccountIds) {
                    $subQuery->where('account_code', AccountEnum::STAFF->value)
                        ->whereIn('id', $staffAccountIds);
                })
                    ->orWhere(function ($subQuery) use ($currentBranchAccountId) {
                        $subQuery->where('account_code', AccountEnum::MAIN->value)
                            ->where('id', $currentBranchAccountId);
                    });
            })
            ->orderBy('account_code', 'asc')
            ->get();
        $paymentTypes = PaymentType::all();

        return view('admin.expenses.edit', [
            'expense' => $expense,
            'items' => $items,
            'places' => $places,
            'accounts' => $accounts,
            'paymentTypes' => $paymentTypes
        ]);
    }

    public function update(Request $request, Expense $expense)
    {
        $data = $request->validate([
            'item_id' => 'required',
            'quantity' => 'required',
            'amount' => 'required',
            'place_id' => 'sometimes',
            'account_id' => 'required',
            'payment_type_id' => 'required',
            'date' => 'nullable|date|date_format:Y-m-d',
            'description' => 'sometimes'
        ]);

        $this->service->update($expense, $data);
        session()->flash("success_alert", __('translation.alert_messages.updated'));
        return redirect()->route('admin.expenses.index');
    }

    public function destroy(Expense $expense)
    {
        $this->service->destroy($expense);
        session()->flash("success_alert", __('translation.alert_messages.deleted'));
        return redirect()->route('admin.expenses.index');
    }

    public function indexSettings()
    {
        return view('admin.expenses.settings.index');
    }

    public function createSettingsItem()
    {
        return view('admin.expenses.settings.create-item');
    }

    public function storeSettingsItem(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'sometimes',
        ]);

        ExpenseItem::create($data);

        session()->flash("success_alert", __('translation.alert_messages.created'));
        return redirect()->route('admin.expenses.settings.index');
    }

    public function editSettingsItem($id)
    {
        $item = ExpenseItem::findOrFail($id);

        return view('admin.expenses.settings.edit-item', compact('item'));
    }

    public function updateSettingsItem(Request $request, $id)
    {
        $item = ExpenseItem::findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'sometimes',
        ]);

        $item->update($data);

        session()->flash("success_alert", __('translation.alert_messages.updated'));
        return redirect()->route('admin.expenses.settings.index');
    }

    public function deleteSettingsItem($id)
    {
        $item = ExpenseItem::findOrFail($id);

        $item->delete();

        session()->flash("success_alert", __('translation.alert_messages.deleted'));
        return redirect()->route('admin.expenses.settings.index');
    }

    public function editSettingsPlace($id)
    {
        $item = ExpensePlace::findOrFail($id);

        return view('admin.expenses.settings.edit-place', compact('item'));
    }

    public function updateSettingsPlace(Request $request, $id)
    {
        $item = ExpensePlace::findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'sometimes',
        ]);

        $item->update($data);

        session()->flash("success_alert", __('translation.alert_messages.updated'));
        return redirect()->route('admin.expenses.settings.index');
    }

    public function deleteSettingsPlace($id)
    {
        $item = ExpensePlace::findOrFail($id);

        $item->delete();

        session()->flash("success_alert", __('translation.alert_messages.deleted'));
        return redirect()->route('admin.expenses.settings.index');
    }

    public function createSettingsPlace()
    {
        return view('admin.expenses.settings.create-place');
    }

    public function storeSettingsPlace(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'sometimes',
        ]);

        ExpensePlace::create($data);

        session()->flash("success_alert", __('translation.alert_messages.created'));
        return redirect()->route('admin.expenses.settings.index');
    }
}
