<div>
    <div>
        <table class="table">
            <tr>
                <th>ID</th>
                <th>@lang("translation.accounts.name")</th>
                <th>@lang("translation.accounts.account_code")</th>
                <th>@lang("translation.accounts.balance")</th>
                <th></th>
            </tr>
            @foreach($otherAccounts as $otherAccount)
                <tr>
                    <td>{{ $otherAccount->id }}</td>
                    <td>
                        <a href="{{ route('admin.accounts.show', $otherAccount->id) }}">
                            {{ $otherAccount->name }} <i class="mdi mdi-open-in-new"></i>
                        </a>
                    </td>
                    <td>{{ $otherAccount->account_code }}</td>
                    <td>{{ money($otherAccount->getBalance()) }}</td>
                    <td>
                        <a href="{{ route('admin.accounts.show', $otherAccount->id) }}">
                            <i class="mdi mdi-eye fs-5"></i>
                        </a>
                    </td>
                </tr>
            @endforeach
        </table>
        {{ $otherAccounts->links() }}
    </div>

</div>
