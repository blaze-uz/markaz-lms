<div class="card rounded-4">
    <div class="card-header rounded-top-4">
        <div class="d-flex justify-content-between align-items-center">
            <h4 class="mb-0 card-title">
                <i class="align-middle ri-bar-chart-2-line me-1"></i>
                @lang('translation.students_statistics')
            </h4>
            <div class="d-flex align-items-center gap-2">
                <div wire:loading>
                    <i class="ri-loader-4-line align-middle animate-spin text-primary"></i>
                </div>
                <button type="button" class="btn btn-soft-info btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#helpModal"
                    title="@lang('translation.help')">
                    <i class="ri-question-line align-middle"></i>
                    @lang('translation.help')
                </button>
                <select class="form-select form-select-sm" style="width: auto;"
                    wire:change="setMonthCount($event.target.value)">
                    @foreach ($monthOptions as $option)
                        <option value="{{ $option }}" {{ $monthCount == $option ? 'selected' : '' }}>
                            {{ $option }} @lang('translation.months.short')
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    <div class="card-body" wire:loading.class="opacity-50">
        <div class="table-responsive">
            <table class="table mb-0 table-bordered">
                <thead class="table-light">
                    <tr class="text-center">
                        <th>
                            <i class="align-middle ri-calendar-line me-1"></i>
                            @lang('translation.month')
                        </th>
                        <th class="text-success">
                            <i class="align-middle ri-user-add-line me-1"></i>
                            @lang('translation.actually_joined')
                        </th>
                        <th class="text-danger">
                            <i class="align-middle ri-user-unfollow-line me-1"></i>
                            @lang('translation.actually_left')
                        </th>
                        <th class="text-info">
                            <i class="align-middle ri-shuffle-line me-1"></i>
                            @lang('translation.transferred')
                        </th>
                        <th>
                            <i class="align-middle ri-scales-3-line me-1"></i>
                            @lang('translation.net_change')
                        </th>
                        <th>
                            <i class="align-middle ri-list-check me-1"></i>
                            @lang('translation.actions')
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($monthsData as $index => $data)
                        @php
                            $netChange = $data['actual_joined'] - $data['actual_left'];
                            $totalClass = $netChange > 0 ? 'text-success' : ($netChange < 0 ? 'text-danger' : '');
                            $yearMonth = $data['year'] . '-' . str_pad($data['month_num'], 2, '0', STR_PAD_LEFT);
                            $hasActivity =
                                $data['actual_joined'] > 0 || $data['actual_left'] > 0 || $data['transferred'] > 0;
                        @endphp
                        <tr class="text-center">
                            <td>{{ $data['month'] }}</td>
                            <td class="text-success">
                                @if ($data['actual_joined'] > 0)
                                    <i class="align-middle ri-add-circle-fill me-1"></i>
                                @endif
                                {{ $data['actual_joined'] }}
                            </td>
                            <td class="text-danger">
                                @if ($data['actual_left'] > 0)
                                    <i class="align-middle ri-close-circle-fill me-1"></i>
                                @endif
                                {{ $data['actual_left'] }}
                            </td>
                            <td class="text-info">
                                @if ($data['transferred'] > 0)
                                    <i class="align-middle ri-shuffle-fill me-1"></i>
                                @endif
                                {{ $data['transferred'] }}
                            </td>
                            <td class="{{ $totalClass }}">
                                @if ($netChange > 0)
                                    <i class="align-middle ri-arrow-up-circle-fill me-1"></i>
                                @elseif($netChange < 0)
                                    <i class="align-middle ri-arrow-down-circle-fill me-1"></i>
                                @endif
                                {{ $netChange }}
                            </td>
                            <td>
                                @if ($hasActivity)
                                    <button type="button" class="btn btn-sm btn-primary"
                                        wire:click="loadStudentsForMonth('{{ $yearMonth }}')" data-bs-toggle="modal"
                                        data-bs-target="#studentDetailsModal">
                                        <i class="ri-eye-line align-middle"></i>
                                        @lang('translation.view')
                                    </button>
                                @else
                                    <span class="badge bg-light text-muted">@lang('translation.no_changes')</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">@lang('translation.no_data')</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Student Details Modal -->
    <div wire:ignore.self class="modal fade" id="studentDetailsModal" tabindex="-1"
        aria-labelledby="studentDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="studentDetailsModalLabel">
                        @lang('translation.student_changes_for') {{ $selectedMonthFormatted }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div wire:loading wire:target="loadStudentsForMonth" class="text-center my-3">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">@lang('translation.loading_students')</p>
                    </div>

                    <div wire:loading.remove wire:target="loadStudentsForMonth">
                        @if (isset($selectedMonthStudents))
                            <ul class="nav nav-tabs nav-tabs-custom nav-justified" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" data-bs-toggle="tab" href="#actual-joined-students"
                                        role="tab">
                                        <span class="d-block d-sm-none"><i class="ri-user-add-line"></i></span>
                                        <span class="d-none d-sm-block">
                                            <i class="ri-user-add-line me-1"></i> @lang('translation.actually_joined')
                                            <span
                                                class="badge bg-success ms-1">{{ count($selectedMonthStudents['actual_joined'] ?? []) }}</span>
                                        </span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#actual-left-students"
                                        role="tab">
                                        <span class="d-block d-sm-none"><i class="ri-user-unfollow-line"></i></span>
                                        <span class="d-none d-sm-block">
                                            <i class="ri-user-unfollow-line me-1"></i> @lang('translation.actually_left')
                                            <span
                                                class="badge bg-danger ms-1">{{ count($selectedMonthStudents['actual_left'] ?? []) }}</span>
                                        </span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#transferred-students"
                                        role="tab">
                                        <span class="d-block d-sm-none"><i class="ri-shuffle-line"></i></span>
                                        <span class="d-none d-sm-block">
                                            <i class="ri-shuffle-line me-1"></i> @lang('translation.transferred')
                                            <span
                                                class="badge bg-info ms-1">{{ count($selectedMonthStudents['transferred'] ?? []) }}</span>
                                        </span>
                                    </a>
                                </li>
                            </ul>

                            <div class="tab-content p-3">
                                <!-- Actually Joined Students Tab -->
                                <div class="tab-pane active" id="actual-joined-students" role="tabpanel">
                                    @if (count($selectedMonthStudents['actual_joined'] ?? []) > 0)
                                        <div class="table-responsive" style="max-height: 400px;">
                                            <table class="table table-hover table-striped mb-0">
                                                <thead class="table-light sticky-top">
                                                    <tr>
                                                        <th>#</th>
                                                        <th>@lang('translation.students.student')</th>
                                                        <th>@lang('translation.groups.group')</th>
                                                        <th>@lang('translation.date_joined')</th>
                                                        <th>@lang('translation.status')</th>
                                                        <th>@lang('translation.actions')</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($selectedMonthStudents['actual_joined'] as $index => $student)
                                                        <tr
                                                            class="{{ $student['is_deleted'] ? 'table-danger' : '' }}">
                                                            <td>{{ $index + 1 }}</td>
                                                            <td>
                                                                <div class="d-flex align-items-center">
                                                                    <span>{{ $student['name'] }}</span>
                                                                </div>
                                                            </td>
                                                            <td>{{ $student['group_name'] }}</td>
                                                            <td>{{ $student['date'] }}</td>
                                                            <td>
                                                                @if ($student['is_deleted'])
                                                                    <span class="badge bg-danger">
                                                                        <i class="ri-delete-bin-line me-1"></i>
                                                                        @lang('translation.deleted')
                                                                    </span>
                                                                    <small
                                                                        class="d-block text-muted">{{ $student['deleted_at'] }}</small>
                                                                @else
                                                                    <span class="badge bg-success">
                                                                        <i class="ri-checkbox-circle-line me-1"></i>
                                                                        @lang('translation.active')
                                                                    </span>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                <a href="{{ route('admin.students.show', $student['id']) }}"
                                                                    class="btn btn-sm btn-soft-primary"
                                                                    target="_blank">
                                                                    <i class="ri-eye-line align-middle"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="alert alert-info mb-0">
                                            <i class="ri-information-line me-2"></i>
                                            @lang('translation.no_students_actually_joined')
                                        </div>
                                    @endif
                                </div>

                                <!-- Actually Left Students Tab -->
                                <div class="tab-pane" id="actual-left-students" role="tabpanel">
                                    @if (count($selectedMonthStudents['actual_left'] ?? []) > 0)
                                        <div class="table-responsive" style="max-height: 400px;">
                                            <table class="table table-hover table-striped mb-0">
                                                <thead class="table-light sticky-top">
                                                    <tr>
                                                        <th>#</th>
                                                        <th>@lang('translation.students.student')</th>
                                                        <th>@lang('translation.groups.group')</th>
                                                        <th>@lang('translation.date_left')</th>
                                                        <th>@lang('translation.status')</th>
                                                        <th>@lang('translation.actions')</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($selectedMonthStudents['actual_left'] as $index => $student)
                                                        <tr
                                                            class="{{ $student['is_deleted'] ? 'table-danger' : '' }}">
                                                            <td>{{ $index + 1 }}</td>
                                                            <td>
                                                                <div class="d-flex align-items-center">
                                                                    <span>{{ $student['name'] }}</span>
                                                                </div>
                                                            </td>
                                                            <td>{{ $student['group_name'] }}</td>
                                                            <td>{{ $student['date'] }}</td>
                                                            <td>
                                                                @if ($student['is_deleted'])
                                                                    <span class="badge bg-danger">
                                                                        <i class="ri-delete-bin-line me-1"></i>
                                                                        @lang('translation.deleted')
                                                                    </span>
                                                                    <small
                                                                        class="d-block text-muted">{{ $student['deleted_at'] }}</small>
                                                                @else
                                                                    <span class="badge bg-success">
                                                                        <i class="ri-checkbox-circle-line me-1"></i>
                                                                        @lang('translation.active')
                                                                    </span>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                <a href="{{ route('admin.students.show', $student['id']) }}"
                                                                    class="btn btn-sm btn-soft-primary"
                                                                    target="_blank">
                                                                    <i class="ri-eye-line align-middle"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="alert alert-info mb-0">
                                            <i class="ri-information-line me-2"></i>
                                            @lang('translation.no_students_actually_left')
                                        </div>
                                    @endif
                                </div>

                                <!-- Transferred Students Tab -->
                                <div class="tab-pane" id="transferred-students" role="tabpanel">
                                    @if (count($selectedMonthStudents['transferred'] ?? []) > 0)
                                        <div class="table-responsive" style="max-height: 400px;">
                                            <table class="table table-hover table-striped mb-0">
                                                <thead class="table-light sticky-top">
                                                    <tr>
                                                        <th>#</th>
                                                        <th>@lang('translation.students.student')</th>
                                                        <th>@lang('translation.from_group')</th>
                                                        <th>@lang('translation.to_group')</th>
                                                        <th>@lang('translation.transfer_date')</th>
                                                        <th>@lang('translation.status')</th>
                                                        <th>@lang('translation.actions')</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($selectedMonthStudents['transferred'] as $index => $student)
                                                        <tr
                                                            class="{{ $student['is_deleted'] ? 'table-danger' : '' }}">
                                                            <td>{{ $index + 1 }}</td>
                                                            <td>
                                                                <div class="d-flex align-items-center">
                                                                    <span>{{ $student['name'] }}</span>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <span
                                                                    class="badge bg-light text-muted">{{ $student['from_group_name'] }}</span>
                                                            </td>
                                                            <td>
                                                                <span
                                                                    class="badge bg-primary">{{ $student['to_group_name'] }}</span>
                                                            </td>
                                                            <td>{{ $student['date'] }}</td>
                                                            <td>
                                                                @if ($student['is_deleted'])
                                                                    <span class="badge bg-danger">
                                                                        <i class="ri-delete-bin-line me-1"></i>
                                                                        @lang('translation.deleted')
                                                                    </span>
                                                                    <small
                                                                        class="d-block text-muted">{{ $student['deleted_at'] }}</small>
                                                                @else
                                                                    <span class="badge bg-success">
                                                                        <i class="ri-checkbox-circle-line me-1"></i>
                                                                        @lang('translation.active')
                                                                    </span>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                <a href="{{ route('admin.students.show', $student['id']) }}"
                                                                    class="btn btn-sm btn-soft-primary"
                                                                    target="_blank">
                                                                    <i class="ri-eye-line align-middle"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="alert alert-info mb-0">
                                            <i class="ri-information-line me-2"></i>
                                            @lang('translation.no_students_transferred')
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @else
                            <div class="alert alert-info mb-0">
                                <i class="ri-information-line me-2"></i>
                                @lang('translation.select_month_to_view_students')
                            </div>
                        @endif
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">@lang('translation.close')</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Help/Instructions Modal -->
    <div class="modal fade" id="helpModal" tabindex="-1" aria-labelledby="helpModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-info-subtle">
                    <h5 class="modal-title text-info" id="helpModalLabel">
                        <i class="ri-information-line me-2"></i>
                        @lang('translation.statistics_help_title')
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-4">
                        <!-- Actually Joined -->
                        <div class="col-12">
                            <div class="card border-success border-opacity-25 h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-start">
                                        <div class="flex-shrink-0">
                                            <div class="avatar-sm">
                                                <div class="avatar-title bg-success-subtle text-success rounded">
                                                    <i class="ri-user-add-line fs-18"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="mb-2 text-success">
                                                <i class="ri-add-circle-fill me-1"></i>
                                                @lang('translation.actually_joined')
                                            </h6>
                                            <p class="text-muted mb-0">
                                                @lang('translation.actually_joined_description')
                                            </p>
                                            <div class="mt-2">
                                                <small class="text-success fw-medium">
                                                    <i class="ri-check-line me-1"></i>
                                                    @lang('translation.example'): @lang('translation.actually_joined_example')
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Actually Left -->
                        <div class="col-12">
                            <div class="card border-danger border-opacity-25 h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-start">
                                        <div class="flex-shrink-0">
                                            <div class="avatar-sm">
                                                <div class="avatar-title bg-danger-subtle text-danger rounded">
                                                    <i class="ri-user-unfollow-line fs-18"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="mb-2 text-danger">
                                                <i class="ri-close-circle-fill me-1"></i>
                                                @lang('translation.actually_left')
                                            </h6>
                                            <p class="text-muted mb-0">
                                                @lang('translation.actually_left_description')
                                            </p>
                                            <div class="mt-2">
                                                <small class="text-danger fw-medium">
                                                    <i class="ri-check-line me-1"></i>
                                                    @lang('translation.example'): @lang('translation.actually_left_example')
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Transferred -->
                        <div class="col-12">
                            <div class="card border-info border-opacity-25 h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-start">
                                        <div class="flex-shrink-0">
                                            <div class="avatar-sm">
                                                <div class="avatar-title bg-info-subtle text-info rounded">
                                                    <i class="ri-shuffle-line fs-18"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="mb-2 text-info">
                                                <i class="ri-shuffle-fill me-1"></i>
                                                @lang('translation.transferred')
                                            </h6>
                                            <p class="text-muted mb-0">
                                                @lang('translation.transferred_description')
                                            </p>
                                            <div class="mt-2">
                                                <small class="text-info fw-medium">
                                                    <i class="ri-check-line me-1"></i>
                                                    @lang('translation.example'): @lang('translation.transferred_example')
                                                </small>
                                            </div>
                                            <div class="mt-2">
                                                <small class="text-info fw-medium">
                                                    <i class="ri-time-line me-1"></i>
                                                    <strong>@lang('translation.note'):</strong> @lang('translation.transfers')
                                                    @lang('translation.calculated_within') 24 @lang('translation.hours')
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Net Change -->
                        <div class="col-12">
                            <div class="card border-warning border-opacity-25 h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-start">
                                        <div class="flex-shrink-0">
                                            <div class="avatar-sm">
                                                <div class="avatar-title bg-warning-subtle text-warning rounded">
                                                    <i class="ri-scales-3-line fs-18"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="mb-2 text-warning">
                                                <i class="ri-scales-3-fill me-1"></i>
                                                @lang('translation.net_change')
                                            </h6>
                                            <p class="text-muted mb-0">
                                                @lang('translation.net_change_description')
                                            </p>
                                            <div class="mt-2">
                                                <small class="text-warning fw-medium">
                                                    <i class="ri-calculator-line me-1"></i>
                                                    @lang('translation.formula'): @lang('translation.net_change_formula')
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Info -->
                    <div class="mt-4 p-3 bg-light rounded">
                        <h6 class="mb-2">
                            <i class="ri-lightbulb-line text-warning me-1"></i>
                            @lang('translation.important_notes')
                        </h6>
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">
                                <i class="ri-arrow-right-s-line text-primary me-1"></i>
                                @lang('translation.help_note_1')
                            </li>
                            <li class="mb-2">
                                <i class="ri-arrow-right-s-line text-primary me-1"></i>
                                @lang('translation.help_note_2')
                            </li>
                            <li class="mb-0">
                                <i class="ri-arrow-right-s-line text-primary me-1"></i>
                                @lang('translation.help_note_3')
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">
                        <i class="ri-check-line me-1"></i>
                        @lang('translation.understood')
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
