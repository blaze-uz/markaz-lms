<div class="row">
    <div class="col-xl-3 col-md-6">
        <a href="{{ route('admin.groups.index') }}">
            <div class="card card-animate rounded-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="mb-0 fw-medium text-muted">@lang("translation.groups.groups")</p>
                            <h2 class="mt-4 ff-secondary fw-semibold">
                                <span class="counter-value" data-target="{{$statusCounts['total']}}">0</span>
                            </h2>
                            <small class="text-muted">
                                @lang("translation.click_to_filter")
                                <i class="fa-solid fa-computer-mouse"></i>
                            </small>
                        </div>
                        <div>
                            <div class="flex-shrink-0 avatar-sm">
                                <span class="avatar-title bg-info-subtle rounded-circle fs-2">
                                    <i class="mdi mdi-account-group text-info"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div><!-- end card body -->
            </div> <!-- end card-->
        </a>
    </div> <!-- end col-->
    <div class="col-xl-3 col-md-6">
        <a href="{{ route('admin.groups.index', ['state' => 'active']) }}">
            <div @class(['card card-animate rounded-4'])>
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="mb-0 fw-medium text-muted">
                                @lang("translation.groups.active_groups")
                            </p>
                            <h2 class="mt-4 ff-secondary fw-semibold">
                                <span class="counter-value" data-target="{{$statusCounts['active']}}">
                                    0
                                </span>
                            </h2>
                            <small class="text-muted">
                                @lang("translation.click_to_filter")
                                <i class="fa-solid fa-computer-mouse"></i>
                            </small>
                        </div>
                        <div class="d-flex flex-column justify-content-between align-items-center">
                            <div class="flex-shrink-0 avatar-sm">
                                <span class="avatar-title bg-success-subtle rounded-circle fs-2">
                                    <i class="mdi mdi-check-circle-outline text-success"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div><!-- end card body -->
            </div> <!-- end card-->
        </a>
    </div> <!-- end col-->
    <div class="col-xl-3 col-md-6">
        <a href="{{ route('admin.groups.index', ['state' => 'demo']) }}">
            <div @class(['card card-animate rounded-4'])>
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="mb-0 fw-medium text-muted">@lang("translation.groups.demo_groups")</p>
                            <h2 class="mt-4 ff-secondary fw-semibold">
                                <span class="counter-value" data-target="{{$statusCounts['demo']}}">
                                    0
                                </span>
                            </h2>
                            <small class="text-muted">
                                @lang("translation.click_to_filter")
                                <i class="fa-solid fa-computer-mouse"></i>
                            </small>
                        </div>
                        <div class="d-flex flex-column justify-content-between align-items-center">
                            <div class="flex-shrink-0 avatar-sm">
                                <span class="avatar-title bg-primary-subtle text-primary rounded-circle fs-2">
                                    <i class="mdi mdi-clock-time-two-outline"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div><!-- end card body -->
            </div>
        </a>
    </div> <!-- end col-->
    <div class="col-xl-3 col-md-6">
        <a href="{{ route('admin.groups.index', ['state' => 'completed']) }}">
            <div @class(['card card-animate rounded-4'])>
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="mb-0 fw-medium text-muted">@lang("translation.groups.completed_groups")</p>
                            <h2 class="mt-4 ff-secondary fw-semibold">
                                <span class="counter-value" data-target="{{$statusCounts['completed']}}">0</span>
                            </h2>
                            <small class="text-muted">
                                @lang("translation.click_to_filter")
                                <i class="fa-solid fa-computer-mouse"></i>
                            </small>
                        </div>
                        <div class="d-flex flex-column justify-content-between align-items-center">
                            <div class="flex-shrink-0 avatar-sm">
                                <span class="avatar-title bg-warning-subtle rounded-circle fs-2">
                                    <i class="mdi mdi-archive-arrow-down-outline text-warning"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div><!-- end card body -->
            </div> <!-- end card-->
        </a>
    </div> <!-- end col-->
</div>
