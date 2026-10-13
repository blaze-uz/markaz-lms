<div class="text-muted mb-2">
    <i class="fa-solid fa-info-circle"></i>
    @lang('translation.payments.payment_cards_description')
</div>
<div class="row h-100">
    @foreach ($paymentTypeCards as $key => $paymentTypeCard)
        <div class="col-lg-3 col-md-6 col-sm-6" wire:click="typeFilter('{{ $key }}')">
            <div class="card card-height-100 rounded-4">
                <div class="card-body cursor-pointer">
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-light text-success rounded-circle fs-3">
                                <i class="ri-money-dollar-circle-fill align-middle"></i>
                            </span>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-uppercase fw-semibold fs-12 text-muted mb-1">{{ $key }}</p>
                            <h4 class="mb-0 text-success">{{ money($paymentTypeCard['payments']) }}</h4>
                            <small @class([
                                'text-danger' => $paymentTypeCard['refunds'] > 0,
                                'text-muted' => $paymentTypeCard['refunds'] == 0,
                            ])>
                                <span class="fw-semibold">
                                    {{ money($paymentTypeCard['refunds']) }}
                                </span>
                                {{ __('translation.payments.refunded') }}
                            </small>
                            <br>
                            <small class="text-muted">
                                @lang('translation.click_to_filter')
                                <i class="fa-solid fa-computer-mouse"></i>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
