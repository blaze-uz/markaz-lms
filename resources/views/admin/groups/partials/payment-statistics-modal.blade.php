<!-- Payment Statistics Modal -->
<div class="modal fade" id="paymentStatisticsModal" tabindex="-1" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">
                    @lang("translation.students.payments")
                </h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3 row">
                    @foreach($paymentStatistics as $key => $paymentStatistic)
                    <div class="col-6 col-sm-3">
                        <div
                            class="p-3 border border-dashed {{ $key === '__refund_sum' ? 'bg-danger bg-opacity-10' : '' }}">
                            <h5 class="mb-1">
                                <div>
                                    {{ money($paymentStatistic) }}
                                </div>
                            </h5>
                            <p class="mb-0 text-muted">
                                {{ $key === '__refund_sum' ? __('translation.payments.refund_sum') : $key }}
                            </p>
                        </div>
                    </div>
                    @endforeach
                </div>
                <table class="table">
                    <thead class="bg-primary-subtle">
                        <tr>
                            <th>ID</th>
                            <th>T/R</th>
                            <th>@lang("translation.students.full_name")</th>
                            <th>@lang("translation.students.payment_amount")</th>
                            <th>@lang("translation.students.discount_amount")</th>
                            <th>@lang("translation.students.payment_type")</th>
                            <th>@lang("attr.created_at")</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($groupPayments)
                        @foreach($groupPayments as $payment)
                        <tr>
                            <td>{{$payment->id}}</td>
                            <td>{{ $loop->index + 1 }}</td>
                            <td>{{$payment->student?->getFullName() ?? '-'}}</td>
                            <td>{{money($payment->amount)}}</td>
                            <td>{{money($payment->payment_discounts->sum('amount'))}}</td>
                            <td>{{$payment->payment_type?->name ?? 'Unknown'}}</td>
                            <td>{{dateFormat($payment->date)}}</td>
                        </tr>
                        @endforeach
                        <tr class="table-light">
                            <td colspan="3" class="text-end fw-bold">@lang("translation.payments.total"):</td>
                            <td class="fw-bold text-nowrap">{{money($groupPayments->sum('amount'))}}</td>
                            <td class="fw-bold text-nowrap">{{money($groupPayments->sum(function ($payment) {return
                                $payment->payment_discounts->sum('amount');}))}}</td>
                            <td colspan="3"></td>
                        </tr>
                        @endif
                    </tbody>
                </table>
                <div class="text-center alert alert-info" role="alert">
                    @lang('translation.payments.payment_amount_without_refund')
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    @lang("translation.Close")
                </button>
            </div>
        </div>
    </div>
</div>
