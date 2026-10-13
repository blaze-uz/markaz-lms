<div>
    <div class="align-items-center d-flex justify-content-between">
        <x-search />
        <div class="d-inline-block">
            <select name="status-staff" id="" class="form-control form-select" wire:model.live="groupStatus">
                <option value="all">
                    @lang("translation.all")
                </option>
                <option value="active">
                    @lang("translation.accounts.active")
                </option>
                <option value="inactive">
                    @lang("translation.accounts.inactive")
                </option>
            </select>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table">
            <tr>
                <th>ID</th>
                <th>@lang("translation.accounts.name")</th>
                <th>@lang("translation.accounts.account_code")</th>
                <th>@lang("translation.accounts.account_code")</th>
                <th>@lang("translation.accounts.balance")</th>
                <th></th>
            </tr>
            @forelse($groupAccounts as $groupAccount)
            <tr>
                <td>{{ $groupAccount->id }}</td>
                <td class="text-nowrap">
                    <a href="{{ route('admin.accounts.show', $groupAccount->id) }}">
                        {{ $groupAccount->name }} <i class="mdi mdi-open-in-new"></i>
                    </a>
                </td>
                <td>
                    <span @class(["badge", "bg-success-subtle text-success"=> $groupAccount->status == 'active',
                        "bg-danger-subtle text-danger"=> $groupAccount->status == 'inactive'
                        ])>
                        {{ __('translation.accounts.'.$groupAccount->status) }}
                    </span>
                </td>
                <td>{{ $groupAccount->account_code }}</td>
                <td class="text-nowrap">{{ money($groupAccount->getBalance()) }}</td>
                <td>
                    <a href="{{ route('admin.accounts.show', $groupAccount->id) }}">
                        <i class="mdi mdi-eye fs-5"></i>
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5">
                    <x-no-result />
                </td>
            </tr>
            @endforelse
        </table>
    </div>
    {{ $groupAccounts->links() }}
</div>