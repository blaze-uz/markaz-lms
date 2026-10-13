<div class="row">
    <div class="col-xl-4 col-md-6">
        <div class="card rounded-4 card-animate">
            <a href="{{ route('admin.students.index') }}">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="fw-medium text-muted mb-0">@lang("translation.students.count")</p>
                            <h2 class="mt-4 ff-secondary fw-semibold">
                                <span class="counter-value" data-target="{{$studentsCount}}">0</span>
                            </h2>
                        </div>
                        <div>
                            <div class="avatar-sm flex-shrink-0">
                                    <span class="avatar-title bg-info-subtle rounded-circle fs-2">
                                        🧑‍🎓
                                    </span>
                            </div>
                        </div>
                    </div>
                </div><!-- end card rounded-4 body -->
            </a>
        </div> <!-- end card-->
    </div> <!-- end col-->
    <div class="col-xl-4 col-md-6">
        <div class="card rounded-4 card-animate">
            <a href="{{ route('admin.groups.index') }}">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="fw-medium text-muted mb-0">@lang("translation.groups.groups_count")</p>
                            <h2 class="mt-4 ff-secondary fw-semibold">
                                <span>{{ $groupsCount }}</span>
                            </h2>
                        </div>
                        <div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-info-subtle rounded-circle fs-2">
                                    🎓
                                </span>
                            </div>
                        </div>
                    </div>
                </div><!-- end card rounded-4 body -->
            </a>
        </div> <!-- end card-->
    </div> <!-- end col-->
    <div class="col-xl-4 col-md-6">
        <div class="card rounded-4 card-animate">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <p class="fw-medium text-muted mb-0">@lang("translation.students.balance")</p>
                        <h2 class="mt-4 ff-secondary fw-semibold">
                            <span @class(['text-nowrap','text-success' => $balance > 0, 'text-danger' => $balance < 0])>
                                {{ money($balance) }}
                            </span>
                        </h2>
                    </div>
                    <div>
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-info-subtle rounded-circle fs-2">
                                💰
                            </span>
                        </div>
                    </div>
                </div>
            </div><!-- end card rounded-4 body -->
        </div> <!-- end card-->
    </div> <!-- end col-->
</div>
