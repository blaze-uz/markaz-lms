<div class="row h-100">
    <div class="col-lg-3 col-md-6">
        <div class="card">
            <div class="cursor-pointer card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 avatar-sm">
                        <span class="avatar-title bg-light text-success rounded-circle fs-4">
                            <i class="align-middle fa-regular fa-circle-check"></i>
                        </span>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <p class="mb-1 text-uppercase fw-semibold fs-12 text-muted">@lang("translation.sms.sent")</p>
                        <h4 class="mb-0 text-success fw-semibold">{{ $card['sent'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card">
            <div class="cursor-pointer card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 avatar-sm">
                        <span class="avatar-title bg-light text-warning rounded-circle fs-4">
                            <i class="fa-regular fa-clock"></i>
                        </span>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <p class="mb-1 text-uppercase fw-semibold fs-12 text-muted">@lang("translation.sms.pending")</p>
                        <h4 class="mb-0 text-warning fw-semibold">{{ $card['pending'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card">
            <div class="cursor-pointer card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 avatar-sm">
                        <span class="avatar-title bg-light text-danger rounded-circle fs-4">
                            <i class="fa-solid fa-ban"></i>
                        </span>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <p class="mb-1 text-uppercase fw-semibold fs-12 text-muted">@lang("translation.sms.failed")</p>
                        <h4 class="mb-0 text-danger">
                            {{ $card['failed'] }}
                        </h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- <div class="col-lg-3 col-md-6">
        <div class="card">
            <div class="cursor-pointer card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 avatar-sm">
                        <span class="avatar-title bg-light text-success rounded-circle fs-4">
                            <i class="fa-solid fa-coins"></i>
                        </span>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <p class="mb-1 text-uppercase fw-semibold fs-12 text-muted">@lang("translation.sms.costs")</p>
                        <h4 class="mb-0 text-success">
                            {{ money($card['price']) }}
                        </h4>
                    </div>
                </div>
            </div>
        </div>
    </div> --}}
</div>