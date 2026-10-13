<div class="d-flex flex-column h-100">
    <div class="row h-100">
        <div class="col-lg-3 col-md-6">
            <div class="card card-animate">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm flex-shrink-0">
                            <span @class(['avatar-title rounded-circle fs-4',
                                'bg-danger-subtle text-danger' => $attendance_percentage < 70,
                                'bg-success-subtle text-success' => $attendance_percentage >= 70])>
                                <i class="fa-solid fa-chart-simple align-middle"></i>
                            </span>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-uppercase fw-semibold fs-12 text-muted mb-1">
                                @lang("translation.reports.attendance")
                            </p>
                            <h4 @class(['mb-0', 'text-success' => $attendance_percentage >= 70, 'text-danger' => $attendance_percentage < 70])>
                                {{ $attendance_percentage }} %
                            </h4>
                        </div>
                    </div>
                </div><!-- end card body -->
            </div><!-- end card -->
        </div><!-- end col -->
        <div class="col-lg-3 col-md-6">
            <div class="card card-animate">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-success-subtle text-success rounded-circle fs-3">
                                <i class="fas fa-users align-middle fs-4"></i>
                            </span>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-uppercase fw-semibold fs-12 text-muted mb-1">
                                @lang("translation.groups.students_count")
                            </p>
                            <h4 class="mb-0 text-success">
                                {{ $students_count }}
                            </h4>
                        </div>
                    </div>
                </div><!-- end card body -->
            </div><!-- end card -->
        </div><!-- end col -->
        <div class="col-lg-3 col-md-6">
            <div class="card card-animate">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-success-subtle text-success rounded-circle fs-3">
                                <i class="fas fa-user-graduate align-middle fs-4"></i>
                            </span>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-uppercase fw-semibold fs-12 text-muted mb-1">
                                @lang("translation.users.teachers_count")
                            </p>
                            <h4 class="mb-0 text-success">
                                {{ $teachers_count }}
                            </h4>
                        </div>
                    </div>
                </div><!-- end card body -->
            </div><!-- end card -->
        </div><!-- end col -->
        <div class="col-lg-3 col-md-6">
            <div class="card card-animate">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-success-subtle text-success rounded-circle fs-3">
                                <i class="fa-solid fa-person-walking-arrow-right fs-4 align-middle"></i>
                            </span>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-uppercase fw-semibold fs-12 text-muted mb-1">
                                @lang("translation.students.deleted_students_count")
                            </p>
                            <h4 class="mb-0 text-success">
                                {{ $deleted_students_count }}
                            </h4>
                        </div>
                    </div>
                </div><!-- end card body -->
            </div><!-- end card -->
        </div><!-- end col -->
    </div>
</div>
