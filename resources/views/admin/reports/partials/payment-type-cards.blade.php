<h5>@lang("translation.payments.type-name")</h5>
<div class="row">
    @foreach($paymentTypes as $key => $paymentType)
        <div class="col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title rounded-circle fs-3 bg-success-subtle text-success">
                                    <i class="ri-money-dollar-circle-fill align-middle"></i>
                                </span>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-uppercase fw-semibold fs-12 text-muted mb-1">
                                {{ $key }}
                            </p>
                            <h4 class="mb-0 text-success">
                                {{ money($paymentType) }}
                            </h4>
                        </div>
                    </div>
                </div><!-- end card body -->
            </div><!-- end card -->
        </div><!-- end col -->
    @endforeach
</div>
