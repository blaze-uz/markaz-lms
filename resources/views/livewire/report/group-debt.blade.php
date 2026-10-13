<div class="card card-height-100">
    <div class="card-header">
        <div class="d-flex justify-content-between">
            <span>
                @lang("translation.reports.debts_by_groups")
            </span>
            <span class="text-danger fw-semibold">
                {{ money($allDebtSum) }}
            </span>
        </div>
    </div>
    <div class="overflow-auto card-body" style="max-height: 400px">
        <table class="table">
            <tr>
                <th>ID</th>
                <th>@lang("translation.groups.name")</th>
                <th>@lang("translation.groups.debts_count")</th>
                <th>@lang("translation.debt_amount")</th>
            </tr>
            @forelse($groups as $group)
            <tr>
                <td>{{ $group->id }}</td>
                <td>
                    <a href="{{ route('admin.groups.show', $group->id) }}" class="text-muted">
                        {{ $group->name }}
                    </a>
                </td>
                <td class="text-danger fw-bold">{{ $group->debts_count }}</td>
                <td class="text-nowrap fw-semibold text-danger">{{ money($group->total_debt) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="100">
                    <x-no-result />
                </td>
            </tr>
            @endforelse
        </table>
    </div>
</div>