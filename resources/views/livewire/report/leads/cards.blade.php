<div class="d-flex flex-column h-100">
    <div class="row h-100">
        @foreach($collections as $collection)
        <div class="col-lg-4 col-md-4">
            <div class="card card-animate">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 avatar-sm">
                            <span class="avatar-title bg-success-subtle text-success rounded-circle fs-3">
                                <i class="align-middle fas fa-users fs-4"></i>
                            </span>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="mb-1 text-uppercase fw-semibold fs-12 text-muted">
                                {{ $collection->name }}
                            </p>
                            <h4 class="mb-0 text-success">
                                {{ $collection->new_tasks_count }} {{__('translation.count')}}
                            </h4>
                        </div>
                    </div>
                </div><!-- end card body -->
            </div><!-- end card -->
        </div><!-- end col -->
        @endforeach
    </div>
</div>