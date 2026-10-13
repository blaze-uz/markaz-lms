<div>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead class="bg-primary-subtle">
            <tr>
                <th>ID</th>
                <th>@lang("translation.users.which_month")</th>
                <th>@lang("translation.users.amount")</th>
                <th>@lang("translation.users.given_amount")</th>
                <th>@lang("attr.created_at")</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            @forelse($salaries as $salary)
                <tr>
                    <td>{{ $salary->id }}</td>
                    <td>{{ dateFormat($salary->date, 'F') }}</td>
                    <td>{{ money($salary->amount) }}</td>
                    <td>{{ money($salary->given_amount) }}</td>
                    <td>{{ dateFormat($salary->created_at) }}</td>
                    <td>
                        <a href="{{ route('admin.users.salaries.show', $salary->id) }}">
                            <span data-bs-toggle="tooltip" title="@lang("translation.btn.show")">
                                <i class="fas fa-eye"></i>
                            </span>
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="100" class="text-center">
                        @lang("translation.no_data")
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
