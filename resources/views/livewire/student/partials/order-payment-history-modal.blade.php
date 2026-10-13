<div>
    <div class="modal fade" id="orderPaymentHistoryModal" tabindex="-1" aria-labelledby="paymentHistoryModalLabel"
        aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="paymentHistoryModalLabel">
                        {{ __('translation.payment_history') }}
                        @if($orderForHistory)
                            <span class="text-muted fw-normal fs-6 ms-2">
                                #{{ $orderForHistory->id }}
                            </span>
                        @endif
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if($orderForHistory)
                        <div class="mb-4">
                            <div class="d-flex justify-content-between mb-2">
                                <span>{{ __('translation.order_date') }}:</span>
                                <strong>{{ dateFormat($orderForHistory->created_at) }}</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>{{ __('translation.order_total') }}:</span>
                                <strong>{{ money($orderForHistory->total_price) }}</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>{{ __('translation.paid_amount') }}:</span>
                                <strong class="text-success">
                                    {{ money($orderForHistory->total_price - $orderForHistory->left_amount) }}
                                </strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>{{ __('translation.left_amount') }}:</span>
                                @if($orderForHistory->left_amount > 0)
                                    <strong class="text-danger">{{ money($orderForHistory->left_amount) }}</strong>
                                @else
                                    <strong class="text-success">{{ __('translation.paid_in_full') }}</strong>
                                @endif
                            </div>
                        </div>

                        <h6 class="fw-bold mb-3">{{ __('translation.payment_details') }}</h6>
                        
                        @if($paymentHistory && $paymentHistory->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered table-striped">
                                    <thead class="table-light">
                                        <tr>
                                            <th>{{ __('translation.date') }}</th>
                                            <th>{{ __('translation.amount') }}</th>
                                            <th>{{ __('translation.payment_method') }}</th>
                                            <th>{{ __('translation.received_by') }}</th>
                                            <th>{{ __('translation.notes') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($paymentHistory as $payment)
                                            <tr>
                                                <td>{{ dateFormat($payment->payment_date ?? $payment->created_at) }}</td>
                                                <td>{{ money($payment->amount) }}</td>
                                                <td>
                                                    @if($payment->paymentType)
                                                        @if($payment->paymentType->icon)
                                                            <i class="mdi {{ $payment->paymentType->icon }} me-1"></i>
                                                        @endif
                                                        {{ $payment->paymentType->name }}
                                                    @else
                                                        {{ __('translation.unknown') }}
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($payment->creator)
                                                        <span class="d-flex align-items-center">
                                                            @if($payment->creator->getFirstMedia('avatar'))
                                                                <img src="{{ $payment->creator->getFirstMediaUrl('avatar', 'thumb') }}" 
                                                                    alt="{{ $payment->creator->name }}" 
                                                                    class="avatar-xs rounded-circle me-2">
                                                            @elseif($payment->creator->getFirstMedia('profile'))
                                                                <img src="{{ $payment->creator->getFirstMediaUrl('profile', 'thumb') }}" 
                                                                    alt="{{ $payment->creator->name }}" 
                                                                    class="avatar-xs rounded-circle me-2">
                                                            @else
                                                                <div class="avatar-xs me-2">
                                                                    <span class="avatar-title rounded-circle bg-primary text-white">
                                                                        {{ substr($payment->creator->name, 0, 1) }}
                                                                    </span>
                                                                </div>
                                                            @endif
                                                            {{ $payment->creator->name }}
                                                        </span>
                                                    @else
                                                        <span class="text-muted">{{ __('translation.system') }}</span>
                                                    @endif
                                                </td>
                                                <td>{{ $payment->notes }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-info">
                                {{ __('translation.no_payment_records') }}
                            </div>
                        @endif
                    @else
                        <div class="alert alert-warning">
                            {{ __('translation.no_order_selected') }}
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        {{ __('translation.close') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>