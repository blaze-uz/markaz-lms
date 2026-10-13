<div class="row h-100">
    <div class="col-lg-3 col-md-6">
        <div class="card">
            <div class="card-body cursor-pointer">
                <div class="d-flex align-items-center">
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title bg-light text-primary rounded-circle fs-4">
                            <i class="ri-checkbox-circle-line"></i>
                        </span>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <p class="text-uppercase fw-semibold fs-12 text-muted mb-1">@lang("translation.leads.all_calls")
                        </p>
                        <h4 class="mb-0 text-primary fw-semibold">{{ $cardData['total'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card">
            <div class="card-body cursor-pointer">
                <div class="d-flex align-items-center">
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title bg-light text-success rounded-circle fs-4">
                            <i class="mdi mdi-phone-check-outline"></i>
                        </span>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <p class="text-uppercase fw-semibold fs-12 text-muted mb-1">@lang("translation.leads.answered")
                        </p>
                        <h4 class="mb-0 text-success fw-semibold">{{ $cardData['answered'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card">
            <div class="card-body cursor-pointer">
                <div class="d-flex align-items-center">
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title bg-light text-danger rounded-circle fs-4">
                            <i class="mdi mdi-phone-cancel-outline"></i>
                        </span>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <p class="text-uppercase fw-semibold fs-12 text-muted mb-1">@lang("translation.leads.declined")
                        </p>
                        <h4 class="mb-0 text-danger">
                            {{ $cardData['declined'] }}
                        </h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
