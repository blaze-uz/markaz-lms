<div>
    {{-- Collapsible Header --}}
    <div class="card mb-3">
        <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-center cursor-pointer" wire:click="toggle" style="cursor: pointer;">
                <div class="d-flex align-items-center gap-3">
                    <div class="avatar-sm">
                        <div class="avatar-title rounded-3 fs-3" style="background-color: #0088cc; color: #fff;">
                            <i class="ri-telegram-line"></i>
                        </div>
                    </div>
                    <div>
                        <h5 class="mb-1">@lang('translation.telegram.connection_overview')</h5>
                        <p class="text-muted mb-0 small">
                            <span class="badge" style="background-color: #28a745; color: #fff;">{{ number_format($connectedStudents) }} @lang('translation.telegram.connected')</span>
                            <span class="badge ms-1" style="background-color: #dc3545; color: #fff;">{{ number_format($notConnectedStudents) }} @lang('translation.telegram.not_connected')</span>
                            <span class="badge ms-1" style="background-color: #0088cc; color: #fff;">{{ $connectionPercentage }}%</span>
                        </p>
                    </div>
                </div>
                <div>
                    <i class="ri-arrow-{{ $isExpanded ? 'up' : 'down' }}-s-line fs-4 text-muted"></i>
                </div>
            </div>
        </div>
    </div>

    @if($isExpanded)
    <div class="row">
        {{-- Summary Cards --}}
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-sm">
                                <div class="avatar-title rounded-3 fs-3" style="background-color: rgba(99, 102, 241, 0.15); color: #6366f1;">
                                    <i class="ri-user-line"></i>
                                </div>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-uppercase fw-medium text-muted mb-1">
                                @lang('translation.students.students')
                            </p>
                            <h4 class="fs-4 mb-0">{{ number_format($totalStudents) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-sm">
                                <div class="avatar-title rounded-3 fs-3" style="background-color: rgba(40, 167, 69, 0.15); color: #28a745;">
                                    <i class="ri-telegram-line"></i>
                                </div>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-uppercase fw-medium text-muted mb-1">
                                @lang('translation.telegram.connected')
                            </p>
                            <h4 class="fs-4 mb-0">{{ number_format($connectedStudents) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-sm">
                                <div class="avatar-title rounded-3 fs-3" style="background-color: rgba(220, 53, 69, 0.15); color: #dc3545;">
                                    <i class="ri-telegram-line"></i>
                                </div>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-uppercase fw-medium text-muted mb-1">
                                @lang('translation.telegram.not_connected')
                            </p>
                            <h4 class="fs-4 mb-0">{{ number_format($notConnectedStudents) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-sm">
                                <div class="avatar-title rounded-3 fs-3" style="background-color: rgba(0, 136, 204, 0.15); color: #0088cc;">
                                    <i class="ri-percent-line"></i>
                                </div>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-uppercase fw-medium text-muted mb-1">
                                @lang('translation.telegram.connection_rate')
                            </p>
                            <h4 class="fs-4 mb-0">{{ $connectionPercentage }}%</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="row">
        {{-- Pie Chart --}}
        <div class="col-xl-4 col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">@lang('translation.telegram.connection_overview')</h5>
                </div>
                <div class="card-body">
                    <div id="telegramConnectionPieChart" wire:ignore></div>
                </div>
            </div>
        </div>

        {{-- Top Groups by Connection Rate --}}
        <div class="col-xl-4 col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">@lang('translation.telegram.top_connected_groups')</h5>
                </div>
                <div class="card-body">
                    @if($topGroups->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>@lang('translation.groups.group')</th>
                                        <th class="text-center">@lang('translation.students.students')</th>
                                        <th class="text-end">%</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($topGroups as $group)
                                        <tr>
                                            <td>
                                                <a href="{{ route('admin.groups.show', $group->id) }}" class="text-body fw-medium">
                                                    {{ $group->name }}
                                                </a>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-success-subtle text-success">{{ $group->connected_students }}</span>
                                                /
                                                <span class="badge bg-secondary-subtle text-secondary">{{ $group->total_students }}</span>
                                            </td>
                                            <td class="text-end">
                                                <span class="badge bg-info">{{ $group->connection_percentage }}%</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <p class="text-muted">@lang('translation.groups.no_groups')</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Bottom Groups by Connection Rate --}}
        <div class="col-xl-4 col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">@lang('translation.telegram.needs_attention_groups')</h5>
                </div>
                <div class="card-body">
                    @if($bottomGroups->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>@lang('translation.groups.group')</th>
                                        <th class="text-center">@lang('translation.students.students')</th>
                                        <th class="text-end">%</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($bottomGroups as $group)
                                        <tr>
                                            <td>
                                                <a href="{{ route('admin.groups.show', $group->id) }}" class="text-body fw-medium">
                                                    {{ $group->name }}
                                                </a>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-success-subtle text-success">{{ $group->connected_students }}</span>
                                                /
                                                <span class="badge bg-secondary-subtle text-secondary">{{ $group->total_students }}</span>
                                            </td>
                                            <td class="text-end">
                                                <span class="badge {{ $group->connection_percentage < 50 ? 'bg-danger' : 'bg-warning' }}">
                                                    {{ $group->connection_percentage }}%
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <p class="text-muted">@lang('translation.groups.no_groups')</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- All Groups Table --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">@lang('translation.telegram.groups_detail')</h5>
                </div>
                <div class="card-body">
                    @if($groupStats->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th wire:click="sortBy('name')" style="cursor: pointer;">
                                            @lang('translation.groups.group')
                                            @if($sortField === 'name')
                                                <i class="ri-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}-s-line"></i>
                                            @endif
                                        </th>
                                        <th wire:click="sortBy('total_students')" class="text-center" style="cursor: pointer;">
                                            @lang('translation.students.students')
                                            @if($sortField === 'total_students')
                                                <i class="ri-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}-s-line"></i>
                                            @endif
                                        </th>
                                        <th wire:click="sortBy('connected_students')" class="text-center" style="cursor: pointer;">
                                            @lang('translation.telegram.connected')
                                            @if($sortField === 'connected_students')
                                                <i class="ri-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}-s-line"></i>
                                            @endif
                                        </th>
                                        <th wire:click="sortBy('not_connected_students')" class="text-center" style="cursor: pointer;">
                                            @lang('translation.telegram.not_connected')
                                            @if($sortField === 'not_connected_students')
                                                <i class="ri-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}-s-line"></i>
                                            @endif
                                        </th>
                                        <th wire:click="sortBy('connection_percentage')" class="text-center" style="cursor: pointer;">
                                            @lang('translation.telegram.connection_rate')
                                            @if($sortField === 'connection_percentage')
                                                <i class="ri-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}-s-line"></i>
                                            @endif
                                        </th>
                                        <th class="text-center">@lang('translation.status')</th>
                                        <th class="text-center text-nowrap" style="min-width: 7rem;">@lang('translation.btn.show')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($groupStats as $index => $group)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <a href="{{ route('admin.groups.show', $group->id) }}" class="text-body fw-medium">
                                                    {{ $group->name }}
                                                </a>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-primary-subtle text-primary">{{ $group->total_students }}</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-success-subtle text-success">{{ $group->connected_students }}</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-danger-subtle text-danger">{{ $group->not_connected_students }}</span>
                                            </td>
                                            <td class="text-center">
                                                <div class="progress" style="height: 20px;">
                                                    <div class="progress-bar {{ $group->connection_percentage >= 75 ? 'bg-success' : ($group->connection_percentage >= 50 ? 'bg-warning' : 'bg-danger') }}" 
                                                         role="progressbar" 
                                                         style="width: {{ $group->connection_percentage }}%"
                                                         aria-valuenow="{{ $group->connection_percentage }}" 
                                                         aria-valuemin="0" 
                                                         aria-valuemax="100">
                                                        {{ $group->connection_percentage }}%
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                @if($group->connection_percentage >= 75)
                                                    <span class="badge bg-success">@lang('translation.telegram.excellent')</span>
                                                @elseif($group->connection_percentage >= 50)
                                                    <span class="badge bg-warning">@lang('translation.telegram.good')</span>
                                                @elseif($group->connection_percentage >= 25)
                                                    <span class="badge bg-danger">@lang('translation.telegram.poor')</span>
                                                @else
                                                    <span class="badge bg-dark">@lang('translation.telegram.critical')</span>
                                                @endif
                                            </td>
                                            <td class="text-center text-nowrap align-middle">
                                                <button
                                                    type="button"
                                                    class="btn btn-primary btn-sm px-3"
                                                    wire:click="openStudentModal({{ $group->id }})"
                                                >
                                                    <i class="ri-eye-line me-1"></i>@lang('translation.btn.show')
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="ri-inbox-line display-4 text-muted"></i>
                            <p class="text-muted mt-3">@lang('translation.groups.no_groups')</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($modalGroupId !== null)
        <div
            class="modal fade show d-block"
            tabindex="-1"
            role="dialog"
            aria-modal="true"
            style="z-index: 1060; background-color: rgba(0, 0, 0, 0.5);"
            wire:click.self="closeStudentModal"
        >
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg" wire:click.stop>
                <div class="modal-content">
                    <div class="modal-header border-bottom">
                        <h5 class="modal-title fw-semibold">{{ $modalGroupTitle }}</h5>
                        <button type="button" class="btn-close" wire:click="closeStudentModal" aria-label="@lang('translation.btn.cancel')"></button>
                    </div>
                    <div class="modal-body pt-3">
                        <p class="text-muted small mb-3">@lang('translation.telegram.students_connection_list')</p>
                        @if($modalGroupStudents->isEmpty())
                            <p class="text-muted mb-0">@lang('translation.groups.no_students')</p>
                        @else
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered table-hover mb-0 align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 3rem;">#</th>
                                            <th>@lang('translation.students.students')</th>
                                            <th class="text-center text-nowrap">@lang('translation.telegram.connection_status')</th>
                                            <th>@lang('translation.telegram.id')</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($modalGroupStudents as $si => $student)
                                            <tr wire:key="telegram-student-{{ $student->id }}">
                                                <td>{{ $si + 1 }}</td>
                                                <td>
                                                    <a href="{{ route('admin.students.show', $student->id) }}" class="text-body fw-medium">
                                                        {{ $student->getFullName() }}
                                                    </a>
                                                </td>
                                                <td class="text-center">
                                                    @if($student->telegram_chat_id)
                                                        <span class="badge bg-success-subtle text-success">@lang('translation.telegram.connected')</span>
                                                    @else
                                                        <span class="badge bg-warning-subtle text-warning">@lang('translation.telegram.not_connected')</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($student->telegram_chat_id)
                                                        <code class="user-select-all">{{ $student->telegram_chat_id }}</code>
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer border-top">
                        <button type="button" class="btn btn-light" wire:click="closeStudentModal">@lang('translation.btn.cancel')</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @push('script-bottom')
        <script>
            // Pie Chart
            let telegramPieOptions = {
                series: @json($chartData['series']),
                chart: {
                    type: 'donut',
                    height: 300
                },
                labels: @json($chartData['labels']),
                colors: ['#28a745', '#dc3545'],
                legend: {
                    position: 'bottom'
                },
                dataLabels: {
                    enabled: true,
                    formatter: function (val) {
                        return Math.round(val) + '%';
                    }
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '65%',
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: '{{ __("translation.students.total") }}',
                                    formatter: function (w) {
                                        return w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                                    }
                                }
                            }
                        }
                    }
                }
            };

            let telegramPieChart = new ApexCharts(document.querySelector("#telegramConnectionPieChart"), telegramPieOptions);
            telegramPieChart.render();
        </script>
    @endpush
    @endif
</div>
