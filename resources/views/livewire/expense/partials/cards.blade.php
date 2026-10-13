<div class="row h-100">
    <div class="col-lg-3 col-md-6">
        <div class="card">
            <div class="card-body cursor-pointer">
                <div class="d-flex align-items-center">
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title bg-light text-danger rounded-circle fs-3">
                            <i class="ri-money-dollar-circle-fill align-middle"></i>
                        </span>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <p class="text-uppercase fw-semibold fs-12 text-muted mb-1">{{ __("translation.all") }}</p>
                        <h4 class="mb-0 text-danger">{{ money($expenseSum) }}</h4>
                        <small class="text-muted">
                            @lang("translation.expenses.filtered_by_date")
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @foreach($paymentTypeCards as $key => $paymentTypeCard)
        <div class="col-lg-3 col-md-6" wire:click="typeFilter('{{$key}}')">
            <div @class(['card card-animate','border-success-subtle border-2' => $paymentTypeName == $key])>
                <div class="card-body cursor-pointer">
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-light text-danger rounded-circle fs-3">
                                <i class="ri-money-dollar-circle-fill align-middle"></i>
                            </span>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-uppercase fw-semibold fs-12 text-muted mb-1">{{ empty($key) ? __("translation.expenses.other") : $key }}</p>
                            <h4 class="mb-0 text-danger">{{ money($paymentTypeCard) }}</h4>
                            <small class="text-muted">
                                @lang("translation.click_to_filter")
                                <i class="fa-solid fa-computer-mouse"></i>
                            </small>
                        </div>
                        @if($paymentTypeName == $key)
                            <i class="mdi mdi-check-circle-outline fs-3 text-success"></i>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
