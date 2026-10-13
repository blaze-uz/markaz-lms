<div class="row">
    <div class="col-md-4 col-sm-12">
        <div class="card card-animate">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 avatar-sm">
                        <span class="avatar-title bg-danger-subtle text-danger rounded-2 fs-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="feather feather-briefcase text-danger">
                                <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                                <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                            </svg>
                        </span>
                    </div>
                    <div class="overflow-hidden flex-grow-1 ms-3">
                        <p class="mb-3 text-uppercase fw-medium text-muted text-truncate">
                            @lang("translation.expenses.expenses")
                        </p>
                        <div class="mb-3 d-flex align-items-center">
                            <h4 class="mb-0 fs-4 flex-grow-1"><span>{{ money($expenses->total_amount) }}</span></h4>
                            <span class="badge bg-danger-subtle text-danger fs-12">{{ $expenses->count }}
                                {{__("translation.count")}}</span>
                        </div>
                        <p class="mb-0 text-muted text-truncate">
                            @lang("translation.expenses.expenses_for_selected_period")
                        </p>
                    </div>
                </div>
            </div><!-- end card body -->
        </div>
    </div>
    <div class="col-md-4 col-sm-12">
        <div class="card card-animate">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 avatar-sm">
                        <span class="avatar-title bg-danger-subtle text-danger rounded-2 fs-2">
                            <span class="mdi mdi-cash-refund"></span>
                        </span>
                    </div>
                    <div class="overflow-hidden flex-grow-1 ms-3">
                        <p class="mb-3 text-uppercase fw-medium text-muted text-truncate">
                            @lang("translation.refunds")
                        </p>
                        <div class="mb-3 d-flex align-items-center">
                            <h4 class="mb-0 fs-4 flex-grow-1"><span>{{ money($refunds->total_amount) }}</span></h4>
                            <span class="badge bg-danger-subtle text-danger fs-12">{{ $refunds->count }}
                                {{__("translation.count")}}</span>
                        </div>
                        <p class="mb-0 text-muted text-truncate">
                            @lang("translation.refunds_for_selected_period")
                        </p>
                    </div>
                </div>
            </div><!-- end card body -->
        </div>
    </div>
    <div class="col-md-4 col-sm-12">
        <div class="card card-animate">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 avatar-sm">
                        <span class="avatar-title bg-danger-subtle text-danger rounded-2 fs-2">
                            <i class="fa-solid fa-percent"></i>
                        </span>
                    </div>
                    <div class="overflow-hidden flex-grow-1 ms-3">
                        <p class="mb-3 text-uppercase fw-medium text-muted text-truncate">
                            @lang("translation.payments.discounts")
                        </p>
                        <div class="mb-3 d-flex align-items-center">
                            <h4 class="mb-0 fs-4 flex-grow-1"><span>{{ money($discounts->total_amount) }}</span></h4>
                            <span class="badge bg-danger-subtle text-danger fs-12">{{ $discounts->count }}
                                {{__("translation.count")}}</span>
                        </div>
                        <p class="mb-0 text-muted text-truncate">
                            @lang("translation.payments.discounts_for_selected_period")
                        </p>
                    </div>
                </div>
            </div><!-- end card body -->
        </div>
    </div>
</div>