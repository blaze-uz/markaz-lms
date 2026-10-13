<div>
    @assets
        <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/daterangepicker.min.css') }}" />
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
        <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    @endassets

        <style>
            .select2-container--default .select2-selection--single {
                height: 38px;
                border: 1px solid #ced4da;
                border-radius: 0.375rem;
            }

            .select2-container--default .select2-selection--single .select2-selection__rendered {
                line-height: 36px;
                padding-left: 12px;
                color: #495057;
            }

            .select2-container--default .select2-selection--single .select2-selection__arrow {
                height: 36px;
                right: 10px;
            }

            .select2-dropdown {
                border: 1px solid #ced4da;
                border-radius: 0.375rem;
            }

            .loading-overlay {
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(255, 255, 255, 0.8);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 1000;
                border-radius: 0.375rem;
            }

            .loading-spinner {
                width: 40px;
                height: 40px;
                border: 4px solid #f3f3f3;
                border-top: 4px solid #405189;
                border-radius: 50%;
                animation: spin 1s linear infinite;
            }

            @keyframes spin {
                0% {
                    transform: rotate(0deg);
                }

                100% {
                    transform: rotate(360deg);
                }
            }

            .table-loading {
                position: relative;
                min-height: 200px;
            }
        </style>

    <!-- Statistics Cards -->
    <div class="row" wire:loading.class="opacity-50" wire:target="selectedTeacher,selectedGroup,searchQuery,date">
        <div class="col-xl-3 col-md-6">
            <div class="card card-animate position-relative">
                <div wire:loading wire:target="selectedTeacher,selectedGroup,searchQuery,date" class="loading-overlay">
                    <div class="loading-spinner"></div>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1 overflow-hidden">
                            <p class="text-uppercase fw-medium text-muted text-truncate mb-0">
                                {{ __('translation.total_students') }}
                            </p>
                        </div>
                        <div class="flex-shrink-0">
                            <h5 class="text-success fs-14 mb-0">
                                <i class="ri-group-line align-middle"></i>
                            </h5>
                        </div>
                    </div>
                    <div class="d-flex align-items-end justify-content-between mt-2">
                        <div>
                            <h4 class="fs-22 fw-semibold ff-secondary mb-0">{{ $stats['total_students'] }}</h4>
                        </div>
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-success-subtle rounded fs-3">
                                <i class="bx bx-group text-success"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card card-animate position-relative">
                <div wire:loading wire:target="selectedTeacher,selectedGroup,searchQuery,date" class="loading-overlay">
                    <div class="loading-spinner"></div>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1 overflow-hidden">
                            <p class="text-uppercase fw-medium text-muted text-truncate mb-0">
                                {{ __('translation.homework') }} - {{ __('translation.completed') }}
                            </p>
                        </div>
                        <div class="flex-shrink-0">
                            <h5 class="text-success fs-14 mb-0">
                                <i class="ri-checkbox-circle-line align-middle"></i>
                            </h5>
                        </div>
                    </div>
                    <div class="d-flex align-items-end justify-content-between mt-2">
                        <div>
                            <h4 class="fs-22 fw-semibold ff-secondary mb-0 text-success">
                                {{ $stats['total_completed'] }}
                            </h4>
                        </div>
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-success-subtle rounded fs-3">
                                <i class="bx bx-check-circle text-success"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card card-animate position-relative">
                <div wire:loading wire:target="selectedTeacher,selectedGroup,searchQuery,date" class="loading-overlay">
                    <div class="loading-spinner"></div>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1 overflow-hidden">
                            <p class="text-uppercase fw-medium text-muted text-truncate mb-0">
                                {{ __('translation.homework') }} - {{ __('translation.missing') }}
                            </p>
                        </div>
                        <div class="flex-shrink-0">
                            <h5 class="text-danger fs-14 mb-0">
                                <i class="ri-close-circle-line align-middle"></i>
                            </h5>
                        </div>
                    </div>
                    <div class="d-flex align-items-end justify-content-between mt-2">
                        <div>
                            <h4 class="fs-22 fw-semibold ff-secondary mb-0 text-danger">
                                {{ $stats['total_not_completed'] }}
                            </h4>
                        </div>
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-danger-subtle rounded fs-3">
                                <i class="bx bx-x-circle text-danger"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card card-animate position-relative">
                <div wire:loading wire:target="selectedTeacher,selectedGroup,searchQuery,date" class="loading-overlay">
                    <div class="loading-spinner"></div>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1 overflow-hidden">
                            <p class="text-uppercase fw-medium text-muted text-truncate mb-0">
                                {{ __('translation.homework') }} {{ __('translation.rate') }}
                            </p>
                        </div>
                        <div class="flex-shrink-0">
                            <h5 class="text-info fs-14 mb-0">
                                <i class="ri-percent-line align-middle"></i>
                            </h5>
                        </div>
                    </div>
                    <div class="d-flex align-items-end justify-content-between mt-2">
                        <div>
                            <h4 class="fs-22 fw-semibold ff-secondary mb-0 text-info">
                                {{ $stats['completion_rate'] }}%
                            </h4>
                        </div>
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-info-subtle rounded fs-3">
                                <i class="bx bx-trending-up text-info"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header">
                    <div class="row g-3 align-items-center">
                        <div class="col-lg-3 col-md-6">
                            <div class="input-group">
                                <span class="input-group-text"><i class="mdi mdi-magnify"></i></span>
                                <input type="text" wire:model.live.debounce.300ms="searchQuery" class="form-control"
                                    placeholder="{{ __('translation.search') }}...">
                            </div>
                        </div>
                        @unless ($lockTeacherId)
                            <div class="col-lg-2 col-md-6" wire:ignore>
                                <select class="form-select" id="homework-teacher-select">
                                    <option value="">{{ __('translation.all_teachers') }}</option>
                                    @foreach ($teachers as $teacher)
                                        <option value="{{ $teacher->id }}"
                                            {{ $selectedTeacher == $teacher->id ? 'selected' : '' }}>{{ $teacher->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endunless
                        <div class="col-lg-3 col-md-6" wire:ignore>
                            <select class="form-select" id="homework-group-select">
                                <option value="">{{ __('translation.all_groups') }}</option>
                                @foreach ($groups as $group)
                                    <option value="{{ $group->id }}"
                                        {{ $selectedGroup == $group->id ? 'selected' : '' }}>
                                        {{ $group->name }}
                                        @if ($group->teacher)
                                            - {{ $group->teacher->name }}
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <button type="button" class="btn btn-outline-primary w-100" id="homework-date-range">
                                <i class="far fa-calendar-alt me-1"></i>
                                <span>{{ $date ? $date : 'Select' }}</span>
                                <i class="fas fa-caret-down ms-1"></i>
                            </button>
                        </div>
                        <div class="col-lg-1 col-md-6">
                            @if ($searchQuery || $selectedGroup || (! $lockTeacherId && $selectedTeacher))
                                <button type="button" class="btn btn-outline-secondary w-100"
                                    wire:click="clearFilters" title="{{ __('translation.clear_filters') }}"
                                    wire:loading.attr="disabled" wire:target="clearFilters">
                                    <span wire:loading.remove wire:target="clearFilters">
                                        <i class="ri-close-line"></i>
                                    </span>
                                    <span wire:loading wire:target="clearFilters">
                                        <i class="ri-loader-4-line"></i>
                                    </span>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive position-relative table-loading" wire:loading.class="opacity-50"
                        wire:target="selectedTeacher,selectedGroup,searchQuery,date">
                        <div wire:loading wire:target="selectedTeacher,selectedGroup,searchQuery,date"
                            class="loading-overlay">
                            <div class="d-flex flex-column align-items-center">
                                <div class="loading-spinner mb-3"></div>
                                <p class="text-muted mb-0">{{ __('translation.loading') }}...</p>
                            </div>
                        </div>
                        <table class="table mb-0 table-centered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>{{ __('translation.students.full_name') }}</th>
                                    <th>{{ __('translation.students.group') }}</th>
                                    <th>{{ __('translation.students.phone') }}</th>
                                    <th class="text-center">{{ __('translation.completed') }}</th>
                                    <th class="text-center">{{ __('translation.missing') }}</th>
                                    <th class="text-center">{{ __('translation.total') }}</th>
                                    <th class="text-center">{{ __('translation.rate') }}</th>
                                    <th class="text-center" width="100">{{ __('translation.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($students as $student)
                                    @php
                                        $completionRate = $student->total_homework > 0
                                            ? round(($student->completed_count / $student->total_homework) * 100, 1)
                                            : 0;
                                    @endphp
                                    <tr>
                                        <td>
                                            <a href="{{ route('admin.students.show', $student->id) }}"
                                                class="text-dark">
                                                {{ $student->name }} {{ $student->surname }}
                                            </a>
                                        </td>
                                        <td>{{ $student->group_name }}</td>
                                        <td>
                                            <a href="tel:{{ $student->phone }}" class="text-primary">
                                                {{ $student->getFormattedPhone() }}
                                            </a>
                                        </td>
                                        <td class="text-center">
                                            @if ($student->completed_count > 0)
                                                <span class="badge bg-success-subtle text-success">
                                                    <i class="ri-check-circle-line me-1"></i>{{ $student->completed_count }}
                                                </span>
                                            @else
                                                <span class="badge bg-light text-muted">0</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if ($student->not_completed_count > 0)
                                                <span class="badge bg-danger-subtle text-danger">
                                                    <i class="ri-close-circle-line me-1"></i>{{ $student->not_completed_count }}
                                                </span>
                                            @else
                                                <span class="badge bg-success-subtle text-success">
                                                    <i class="ri-check-circle-line me-1"></i>0
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-info-subtle text-info">
                                                {{ $student->total_homework }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar {{ $completionRate >= 75 ? 'bg-success' : ($completionRate >= 50 ? 'bg-warning' : 'bg-danger') }}"
                                                    role="progressbar" style="width: {{ $completionRate }}%"
                                                    aria-valuenow="{{ $completionRate }}" aria-valuemin="0"
                                                    aria-valuemax="100">{{ $completionRate }}%</div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('admin.students.show', $student->id) }}"
                                                class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
                                                data-bs-placement="top" title="{{ __('translation.show') }}">
                                                <i class="mdi mdi-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">
                                            <div class="d-flex flex-column align-items-center justify-content-center py-4">
                                                <i class="ri-book-open-line fs-1 text-muted mb-2"></i>
                                                <p class="text-muted mb-0">{{ __('translation.no_data') }}</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3" wire:loading.class="opacity-50"
                        wire:target="selectedTeacher,selectedGroup,searchQuery,date">
                        {{ $students->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    @script
        <script>
            if (!document.getElementById('homework-select2-css')) {
                var s2css = document.createElement('link');
                s2css.id = 'homework-select2-css';
                s2css.rel = 'stylesheet';
                s2css.href = 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css';
                document.head.appendChild(s2css);
            }

            const initHomeworkFilters = function () {
            moment.locale('{{ app()->getLocale() == 'ru' ? 'ru' : 'uz-latn' }}');

            let currentDate = '{{ $date }}';
            let [startDate, endDate] = currentDate.split(' - ').map(date => moment(date, 'YYYY.MM.DD'));

            $('#homework-date-range span').html(currentDate);

            $('#homework-date-range').daterangepicker({
                    ranges: {
                        "{{ __('attr.today') }}": [moment(), moment()],
                        "{{ __('attr.yesterday') }}": [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                        "{{ __('attr.last7days') }}": [moment().subtract(6, 'days'), moment()],
                        "{{ __('attr.last30days') }}": [moment().subtract(29, 'days'), moment()],
                        "{{ __('attr.thisMonth') }}": [moment().startOf('month'), moment().endOf('month')],
                        "{{ __('attr.lastMonth') }}": [
                            moment().subtract(1, 'months').startOf('month'),
                            moment().subtract(1, 'months').endOf('month')
                        ],
                        "{{ __('attr.thisYear') }}": [moment().startOf('year'), moment().endOf('year')],
                        "{{ __('attr.lastYear') }}": [
                            moment().subtract(1, 'years').startOf('year'),
                            moment().subtract(1, 'years').endOf('year')
                        ],
                    },
                    startDate: startDate,
                    endDate: endDate,
                    locale: {
                        "customRangeLabel": "{{ __('attr.optionalDate') }}",
                        "applyLabel": "{{ __('attr.save') }}",
                        "cancelLabel": "{{ __('attr.cancel') }}",
                    }
                },
                function(start, end) {
                    let selected = start.format('YYYY.MM.DD') + ' - ' + end.format('YYYY.MM.DD');
                    $wire.set('date', selected);
                    $('#homework-date-range span').html(selected);
                }
            );

            function initHomeworkSelect2() {
                const $teacher = $('#homework-teacher-select');
                if ($teacher.length) {
                    if ($teacher.hasClass('select2-hidden-accessible')) {
                        $teacher.select2('destroy');
                    }
                    $teacher.select2({
                        placeholder: '{{ __('translation.all_teachers') }}',
                        allowClear: true,
                        width: '100%'
                    }).on('change', function(e) {
                        let value = $(this).val() || '';
                        $wire.set('selectedTeacher', value);
                    });
                }

                if ($('#homework-group-select').hasClass('select2-hidden-accessible')) {
                    $('#homework-group-select').select2('destroy');
                }

                $('#homework-group-select').select2({
                    placeholder: '{{ __('translation.all_groups') }}',
                    allowClear: true,
                    width: '100%'
                }).on('change', function(e) {
                    let value = $(this).val() || '';
                    $wire.set('selectedGroup', value);
                });
            }

            initHomeworkSelect2();
            };

            (function waitForFilters(attempts) {
                if (document.getElementById('homework-date-range')) {
                    initHomeworkFilters();
                    return;
                }
                if (attempts <= 0) {
                    return;
                }
                setTimeout(function () {
                    waitForFilters(attempts - 1);
                }, 50);
            })(40);

            window.addEventListener('filtersCleared', function() {
                if ($('#homework-teacher-select').length) {
                    $('#homework-teacher-select').val('').trigger('change');
                }
                $('#homework-group-select').val('').trigger('change');
            });
        </script>
    @endscript
</div>
