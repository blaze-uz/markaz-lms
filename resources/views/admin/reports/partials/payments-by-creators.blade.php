<div class="card card-height-100">
    <div class="card-header">
        @lang("translation.reports.payments_by_creators")
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <tr>
                    <th>ID</th>
                    <th>@lang("translation.users.name")</th>
                    <th>@lang("translation.users.payment_amount")</th>
                </tr>
                @foreach($paymentsByCreators as $creator)
                    <tr>
                        <td>{{ $loop->index + 1 }}</td>
                        <td>
                            {{ $creator->name }}
                        </td>
                        <td class="text-nowrap text-success fw-semibold">
                            {{ money($creator->payments_sum_amount) }}
                        </td>
                    </tr>
                @endforeach
            </table>
        </div>
    </div>
</div>
