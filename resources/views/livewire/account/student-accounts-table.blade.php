<div>
    <div class="align-items-center d-flex justify-content-between">
        <x-search />
        <div class="d-inline-block">
            <select name="status-staff" id="" class="form-control form-select" wire:model.live="status">
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
    <table class="table">
        <tr>
            <th>ID</th>
            <th>@lang("translation.accounts.name")</th>
            <th>@lang("translation.accounts.status")</th>
            <th>@lang("translation.accounts.account_code")</th>
            <th>@lang("translation.accounts.balance")</th>
            <th></th>
        </tr>
        @forelse($studentAccounts as $studentAccount)
        <tr>
            <td>{{ $studentAccount->id }}</td>
            <td>
                <a href="{{ route('admin.accounts.show', $studentAccount->id) }}">
                    {{ $studentAccount->name }} <i class="mdi mdi-open-in-new"></i>
                </a>
            </td>
            <td>
                <span @class(["badge", "bg-success-subtle text-success"=> $studentAccount->status == 'active',
                    "bg-danger-subtle text-danger"=> $studentAccount->status == 'inactive'
                    ])>
                    {{ __('translation.accounts.'.$studentAccount->status) }}
                </span>
            </td>
            <td>{{ $studentAccount->account_code }}</td>
            <td>{{ money($studentAccount->getBalance()) }}</td>
            <td>
                <a href="{{ route('admin.accounts.show', $studentAccount->id) }}">
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
    {{ $studentAccounts->links() }}
</div>