<div>
    <div class="row">
        <div @class([ 'col-xl-3 col-md-6'=> !auth()->user()->hasRole('Teacher'),
            'col-xl-6 col-md-6' => auth()->user()->hasRole('Teacher')
            ])>
            <a href="{{ route('admin.students.index') }}">
                <div class="card card-animate rounded-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <p class="mb-0 fw-medium text-muted">@lang("translation.students.count")</p>
                                <h2 class="mt-4 ff-secondary fw-semibold">
                                    <span class="counter-value" data-target="{{$counts['studentsCount']}}">0</span>
                                </h2>
                                <small class="text-muted">
                                    @lang("translation.click_to_filter")
                                    <i class="fa-solid fa-computer-mouse"></i>
                                </small>
                            </div>
                            <div>
                                <div class="flex-shrink-0 avatar-sm">
                                    <span class="avatar-title bg-info-subtle rounded-circle fs-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round"
                                            class="feather feather-users text-info">
                                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                            <circle cx="9" cy="7" r="4"></circle>
                                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                        </svg>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div><!-- end card body -->
                </div> <!-- end card-->
            </a>
        </div> <!-- end col-->
        @unlessrole('Teacher')
        <div class="col-xl-3 col-md-6">
            <a href="{{ route('admin.students.index', ['status' => 'inactive']) }}">
                <div @class(['card card-animate rounded-4'])>
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <p class="mb-0 fw-medium text-muted">
                                    @lang("translation.students.deleted_count")
                                </p>
                                <h2 class="mt-4 ff-secondary fw-semibold">
                                    <span class="counter-value" data-target="{{$counts['deletedStudentsCount']}}">
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
                                    <span class="avatar-title bg-warning-subtle rounded-circle fs-2">
                                        <span class="mdi mdi-account-arrow-left-outline text-warning"></span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div><!-- end card body -->
                </div> <!-- end card-->
            </a>
        </div> <!-- end col-->
        <div class="col-xl-3 col-md-6">
            <a href="{{ route('admin.students.index', ['group_id' => 'null']) }}">
                <div @class(['card card-animate rounded-4'])>
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <p class="mb-0 fw-medium text-muted">@lang("translation.students.unattached_students")
                                </p>
                                <h2 class="mt-4 ff-secondary fw-semibold">
                                    <span class="counter-value" data-target="{{$counts['unattachedStudentsCount']}}">
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
                                        <i class="mdi mdi-account-alert"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div><!-- end card body -->
                </div>
            </a>
        </div> <!-- end col-->
        @endunlessrole
        <div @class([ 'col-xl-3 col-md-6'=> !auth()->user()->hasRole('Teacher'),
            'col-xl-6 col-md-6' => auth()->user()->hasRole('Teacher')
            ])>
            <a href="{{ route('admin.students.index', ['status' => 'debt']) }}">
                <div @class(['card card-animate rounded-4'])>
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <p class="mb-0 fw-medium text-muted">@lang("translation.students.debt_students")</p>
                                <h2 class="mt-4 ff-secondary fw-semibold">
                                    <span class="counter-value" data-target="{{$counts['debtStudentsCount']}}">0</span>
                                </h2>
                                <small class="text-muted">
                                    @lang("translation.click_to_filter")
                                    <i class="fa-solid fa-computer-mouse"></i>
                                </small>
                            </div>
                            <div class="d-flex flex-column justify-content-between align-items-center">
                                <div class="flex-shrink-0 avatar-sm">
                                    <span class="avatar-title bg-danger-subtle rounded-circle fs-2">
                                        <i class="fa-solid fa-scale-unbalanced text-danger"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div><!-- end card body -->
                </div> <!-- end card-->
            </a>
        </div> <!-- end col-->
    </div>
</div>
