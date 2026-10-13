<div>
    @push('css')
        <style>
            .attendance-table-wrap {
                position: relative;
                min-height: 200px;
            }

            .attendance-loading-overlay {
                position: absolute;
                inset: 0;
                background: rgba(255, 255, 255, 0.82);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 10;
                border-radius: 0.5rem;
            }

            .attendance-table-sticky-col {
                position: sticky;
                left: 0;
                z-index: 2;
                background: var(--vz-body-bg, #fff);
                box-shadow: 4px 0 8px -4px rgba(0, 0, 0, 0.08);
            }

            .attendance-table-sticky-col-2 {
                position: sticky;
                left: 3rem;
                z-index: 2;
                background: var(--vz-body-bg, #fff);
                box-shadow: 4px 0 8px -4px rgba(0, 0, 0, 0.08);
            }

            [data-bs-theme="dark"] .attendance-table-sticky-col,
            [data-bs-theme="dark"] .attendance-table-sticky-col-2 {
                background: var(--vz-body-bg, #212529);
            }

            .attendance-table-head-days th {
                white-space: nowrap;
                min-width: 3.25rem;
                font-size: 0.75rem;
                vertical-align: bottom;
            }

            .attendance-cell-badge {
                min-width: 2.25rem;
            }

            .attendance-header-filters {
                display: flex;
                flex-direction: row;
                flex-wrap: nowrap;
                align-items: center;
                gap: 0.5rem;
                max-width: 100%;
                overflow-x: auto;
                overflow-y: visible;
                -webkit-overflow-scrolling: touch;
            }

            .attendance-header-filters .form-select {
                width: auto !important;
            }

            .attendance-header-filters .attendance-filter-dates {
                display: flex;
                flex-wrap: nowrap;
                align-items: center;
                gap: 0.35rem;
                flex: 0 0 auto;
            }

            .attendance-header-filters .attendance-filter-dates .form-control {
                width: auto;
                min-width: 8.75rem;
                max-width: 10.5rem;
                flex-shrink: 0;
            }

            .attendance-header-filters .attendance-filter-dates .attendance-date-sep {
                flex-shrink: 0;
                line-height: 1;
            }

            .attendance-header-filters .attendance-filter-group {
                flex: 1 1 8rem;
                min-width: 7rem;
                max-width: 24rem;
            }

            .attendance-stat-card {
                transition: transform 0.2s ease, box-shadow 0.2s ease;
                border-left: 4px solid transparent;
            }

            .attendance-stat-card:hover {
                transform: translateY(-1px);
                box-shadow: 0 0.35rem 0.85rem rgba(15, 34, 58, 0.06) !important;
            }

            .attendance-stat-card--present {
                border-left-color: rgba(10, 179, 156, 0.85);
            }

            .attendance-stat-card--absent {
                border-left-color: rgba(240, 101, 72, 0.85);
            }

            .attendance-stat-card--students {
                border-left-color: rgba(64, 81, 137, 0.55);
            }

            .attendance-stat-card--rate {
                border-left-color: rgba(64, 160, 252, 0.85);
            }
        </style>
    @endpush

    <div class="card rounded-4 border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-2 rounded-top-4">
            <div class="d-flex flex-column flex-md-row align-items-stretch align-items-md-center justify-content-between gap-2 mb-2">
                <h5 class="card-title mb-0 fs-16 fw-semibold text-dark flex-shrink-0">
                    <i class="ri-calendar-check-line align-middle me-1 text-primary"></i>
                    @lang('translation.groups.attendance')
                </h5>
                @if (($groups && count($groups)) || $group)
                    <div
                        class="d-flex flex-row attendance-header-filters ms-md-auto pb-1 flex-grow-1 flex-md-grow-0 mr-3"
                        style="min-width: 0;">
                        <div class="attendance-filter-dates d-flex flex-row align-items-center gap-2">
                            <button type="button" id="attendance-date-range"
                                class="btn btn-sm btn-outline-primary d-inline-flex align-items-center text-nowrap">
                                <i class="ri-calendar-2-line me-1"></i>
                                <span class="label">{{ \Carbon\Carbon::parse($dateFrom)->format('Y.m.d') }} - {{ \Carbon\Carbon::parse($dateTo)->format('Y.m.d') }}</span>
                                <i class="ri-arrow-down-s-line ms-1"></i>
                            </button>
                        </div>
                        @if ($groups && count($groups))
                            <select name="attendance_group" wire:model.live="selectedGroup"
                                class="form-select form-select-sm attendance-filter-group">
                                @foreach ($groups as $groupOption)
                                    <option value="{{ $groupOption->id }}">
                                        {{ $groupOption->name }}
                                    </option>
                                @endforeach
                            </select>
                        @endif
                    </div>
                @endif
            </div>
            @if ($dateRangeClamped)
                <div class="alert alert-warning py-2 px-3 mb-0 mt-2 fs-12" role="alert">
                    {{ __('translation.groups.attendance_max_date_range_days', ['days' => \App\Livewire\Group\AttendanceTable::MAX_RANGE_DAYS]) }}
                </div>
            @endif
            @if (count($tableRows))
                <div class="d-flex flex-wrap gap-2 align-items-center mt-1">
                    <span class="badge rounded-pill bg-success-subtle text-success border border-success border-opacity-25">
                        <i class="ri-checkbox-circle-line me-1"></i>@lang('attr.present')
                    </span>
                    <span class="badge rounded-pill bg-danger-subtle text-danger border border-danger border-opacity-25">
                        <i class="ri-close-circle-line me-1"></i>@lang('attr.absent')
                    </span>
                    <span class="badge rounded-pill bg-light text-muted border">
                        @lang('attr.not_available')
                    </span>
                </div>
            @endif
        </div>

        @if (count($tableRows))
            <div class="card-body py-2">
                <div class="row g-2 mb-3 align-items-stretch" wire:loading.class="opacity-50"
                    wire:target="dateFrom,dateTo,selectedGroup">
                    <div class="col-xl-3 col-md-6">
                        <div class="card attendance-stat-card attendance-stat-card--present border-0 shadow-sm h-100 mb-0">
                            <div class="card-body p-3 d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                                    <p class="text-uppercase fw-semibold text-muted fs-11 mb-0">
                                        @lang('attr.present')
                                    </p>
                                    <span class="avatar-xs flex-shrink-0">
                                        <span class="avatar-title bg-success-subtle text-success rounded-2 fs-14" style="height: 1.75rem; width: 1.75rem;">
                                            <i class="ri-user-follow-line"></i>
                                        </span>
                                    </span>
                                </div>
                                <p class="fs-2 fw-bold text-success mb-0 lh-1">
                                    {{ $attendanceStats['present'] }}
                                </p>
                                <p class="text-muted fs-11 mb-0 mt-2 lh-sm">
                                    @lang('translation.groups.attendance_total_present_marks')
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card attendance-stat-card attendance-stat-card--absent border-0 shadow-sm h-100 mb-0">
                            <div class="card-body p-3 d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                                    <p class="text-uppercase fw-semibold text-muted fs-11 mb-0">
                                        @lang('attr.absent')
                                    </p>
                                    <span class="avatar-xs flex-shrink-0">
                                        <span class="avatar-title bg-danger-subtle text-danger rounded-2 fs-14" style="height: 1.75rem; width: 1.75rem;">
                                            <i class="ri-user-unfollow-line"></i>
                                        </span>
                                    </span>
                                </div>
                                <p class="fs-2 fw-bold text-danger mb-0 lh-1">
                                    {{ $attendanceStats['absent'] }}
                                </p>
                                <p class="text-muted fs-11 mb-0 mt-2 lh-sm">
                                    @lang('translation.groups.attendance_total_absent_marks')
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card attendance-stat-card attendance-stat-card--students border-0 shadow-sm h-100 mb-0">
                            <div class="card-body p-3 d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                                    <p class="text-uppercase fw-semibold text-muted fs-11 mb-0">
                                        @lang('translation.total_students')
                                    </p>
                                    <span class="avatar-xs flex-shrink-0">
                                        <span class="avatar-title bg-primary-subtle text-primary rounded-2 fs-14" style="height: 1.75rem; width: 1.75rem;">
                                            <i class="ri-group-line"></i>
                                        </span>
                                    </span>
                                </div>
                                <p class="fs-2 fw-bold text-dark mb-0 lh-1">
                                    {{ $attendanceStats['students_count'] }}
                                </p>
                                <p class="text-muted fs-11 mb-0 mt-2 lh-sm">
                                    @lang('translation.groups.group')
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card attendance-stat-card attendance-stat-card--rate border-0 shadow-sm h-100 mb-0">
                            <div class="card-body p-3 d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                                    <p class="text-uppercase fw-semibold text-muted fs-11 mb-0">
                                        @lang('translation.percentage')
                                    </p>
                                    <span class="avatar-xs flex-shrink-0">
                                        <span class="avatar-title bg-info-subtle text-info rounded-2 fs-14" style="height: 1.75rem; width: 1.75rem;">
                                            <i class="ri-percent-line"></i>
                                        </span>
                                    </span>
                                </div>
                                <p class="fs-2 fw-bold text-info mb-0 lh-1">
                                    {{ $attendanceStats['rate'] }}<span class="fs-5 fw-semibold">%</span>
                                </p>
                                <p class="text-muted fs-11 mb-0 mt-2 lh-sm">
                                    {{ __('translation.groups.attendance_summary_subtitle', [
                                        'days' => $attendanceStats['days_count'],
                                        'marks' => $attendanceStats['marked'],
                                    ]) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="attendance-table-wrap">
                    <div wire:loading
                        wire:target="dateFrom,dateTo,selectedGroup"
                        class="attendance-loading-overlay">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                    <div class="table-responsive rounded border">
                        <table class="table table-hover table-sm table-nowrap align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center attendance-table-sticky-col fw-semibold" style="width: 3rem;">#</th>
                                    <th class="attendance-table-sticky-col-2 fw-semibold" style="min-width: 10rem;">
                                        @lang('translation.students.full_name')
                                    </th>
                                    @foreach ($uniqueDays as $day)
                                        @php
                                            $attendanceTakenAt = $attendanceTakenTimes->get($day);
                                        @endphp
                                        <th class="text-center text-muted attendance-table-head-days"
                                            @if ($attendanceTakenAt)
                                                data-attendance-tooltip="true"
                                                data-bs-toggle="tooltip"
                                                title="{{ __('attr.created_at') }}: {{ dateFormat($attendanceTakenAt, 'd.m.Y H:i') }}"
                                            @endif>
                                            {{ dateFormat($day, 'd') }}<br><span
                                                class="fw-normal">{{ dateFormat($day, 'M') }}</span>
                                        </th>
                                    @endforeach
                                    <th class="text-center text-success fw-semibold text-nowrap fs-12" style="min-width: 3.5rem;">
                                        @lang('attr.present')</th>
                                    <th class="text-center text-danger fw-semibold text-nowrap fs-12" style="min-width: 3.5rem;">
                                        @lang('attr.absent')</th>
                                    <th class="text-center text-info fw-semibold text-nowrap fs-12" style="min-width: 4rem;">
                                        @lang('translation.percentage')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($tableRows as $row)
                                    @php
                                        $student = $row['student'];
                                        $rowPresent = $row['present'];
                                        $rowAbsent = $row['absent'];
                                        $rowMarked = $row['marked'];
                                        $rowRate = $row['rate'];
                                    @endphp
                                    <tr wire:key="att-row-{{ $student->id }}">
                                        <td class="text-center text-muted attendance-table-sticky-col">{{ $loop->iteration }}</td>
                                        <td class="attendance-table-sticky-col-2">
                                            <a href="{{ route('admin.students.show', $student->id) }}"
                                                class="text-reset fw-medium text-truncate d-inline-block" style="max-width: 14rem;">
                                                @if ($student->trashed())
                                                    <del data-bs-toggle="tooltip"
                                                        title="{{ __('translation.students.deleted') }}">
                                                        {{ $student->getFullName() }}
                                                    </del>
                                                @else
                                                    {{ $student->getFullName() }}
                                                @endif
                                            </a>
                                        </td>
                                        @foreach ($uniqueDays as $day)
                                            @php
                                                $attendance = $row['cells'][$day] ?? null;
                                            @endphp
                                            <td class="text-center p-1">
                                                @if ($attendance?->status)
                                                    @if ($attendance->status->value == 'present')
                                                        <span class="attendance-cell-badge d-inline-flex align-items-center justify-content-center rounded-circle bg-success-subtle text-success"
                                                            style="width:2rem;height:2rem;">
                                                            <i class="ri-check-line fs-16"></i>
                                                        </span>
                                                    @else
                                                        <span class="attendance-cell-badge d-inline-flex align-items-center justify-content-center rounded-circle bg-danger-subtle text-danger"
                                                            style="width:2rem;height:2rem;">
                                                            <i class="ri-close-line fs-16"></i>
                                                        </span>
                                                    @endif
                                                @else
                                                    <span class="text-muted fs-12">—</span>
                                                @endif
                                            </td>
                                        @endforeach
                                        <td class="text-center">
                                            @if ($rowPresent > 0)
                                                <span class="badge bg-success-subtle text-success">{{ $rowPresent }}</span>
                                            @else
                                                <span class="badge bg-light text-muted">0</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if ($rowAbsent > 0)
                                                <span class="badge bg-danger-subtle text-danger">{{ $rowAbsent }}</span>
                                            @else
                                                <span class="badge bg-light text-muted">0</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if ($rowMarked > 0)
                                                <div class="progress mx-auto" style="height: 1.35rem; max-width: 4.5rem;">
                                                    <div
                                                        class="progress-bar {{ $rowRate >= 75 ? 'bg-success' : ($rowRate >= 50 ? 'bg-warning' : 'bg-danger') }}"
                                                        role="progressbar"
                                                        style="width: {{ $rowRate }}%"
                                                        aria-valuenow="{{ $rowRate }}"
                                                        aria-valuemin="0"
                                                        aria-valuemax="100">
                                                    </div>
                                                </div>
                                                <span class="fs-11 text-muted">{{ $rowRate }}%</span>
                                            @else
                                                <span class="text-muted fs-12">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @else
            <div class="card-body py-5">
                <x-no-result />
            </div>
        @endif

        <div class="card-preloader d-none" wire:loading.class.remove="d-none" wire:target="dateFrom,dateTo,selectedGroup">
            <div class="card-status">
                <div class="spinner-border text-success">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        </div>
    </div>

    @script
        <script>
            if (!document.getElementById('attendance-daterangepicker-css')) {
                var css = document.createElement('link');
                css.id = 'attendance-daterangepicker-css';
                css.rel = 'stylesheet';
                css.href = '{{ asset('assets/css/daterangepicker.min.css') }}';
                document.head.appendChild(css);
            }

            const initAttendancePicker = function () {
                const el = document.getElementById('attendance-date-range');
                if (!el || typeof $.fn.daterangepicker !== 'function' || $(el).data('daterangepicker')) {
                    return;
                }

                moment.locale('{{ app()->getLocale() == 'ru' ? 'ru' : 'uz-latn' }}');

                $(el).daterangepicker({
                    startDate: moment('{{ $dateFrom }}', 'YYYY-MM-DD'),
                    endDate: moment('{{ $dateTo }}', 'YYYY-MM-DD'),
                    maxSpan: { days: {{ \App\Livewire\Group\AttendanceTable::MAX_RANGE_DAYS }} },
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
                    },
                    locale: {
                        "customRangeLabel": "{{ __('attr.optionalDate') }}",
                        "applyLabel": "{{ __('attr.save') }}",
                        "cancelLabel": "{{ __('attr.cancel') }}",
                    }
                }, function (start, end) {
                    $('#attendance-date-range .label').text(start.format('YYYY.MM.DD') + ' - ' + end.format('YYYY.MM.DD'));
                    $wire.set('dateFrom', start.format('YYYY-MM-DD'), false);
                    $wire.set('dateTo', end.format('YYYY-MM-DD'));
                });
            };

            const initAttendanceTooltips = function () {
                if (!window.bootstrap || !bootstrap.Tooltip) {
                    return;
                }

                document.querySelectorAll('[data-attendance-tooltip="true"]').forEach(function (el) {
                    bootstrap.Tooltip.getOrCreateInstance(el);
                });
            };

            if (window.Livewire && !window.__attendanceTableTooltipHooked) {
                window.__attendanceTableTooltipHooked = true;
                Livewire.hook('morph.updated', function () {
                    setTimeout(initAttendanceTooltips, 0);
                });
            }

            const loadScript = function (src) {
                return new Promise(function (resolve) {
                    var s = document.createElement('script');
                    s.src = src;
                    s.onload = function () { resolve(); };
                    s.onerror = function () { resolve(); };
                    document.head.appendChild(s);
                });
            };

            const waitFor = function (cond) {
                return new Promise(function (resolve) {
                    var n = 100;
                    (function check() {
                        if (cond() || n-- <= 0) {
                            resolve();
                            return;
                        }
                        setTimeout(check, 50);
                    })();
                });
            };

            // Load moment fully, then daterangepicker (so it binds with moment + jQuery present), then init.
            if (typeof window.moment === 'undefined') {
                await loadScript('https://cdn.jsdelivr.net/momentjs/latest/moment.min.js');
            }
            if (!(window.jQuery && $.fn && $.fn.daterangepicker)) {
                await loadScript('https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js');
            }
            await waitFor(function () {
                return typeof window.moment !== 'undefined'
                    && window.jQuery && $.fn && typeof $.fn.daterangepicker === 'function'
                    && document.getElementById('attendance-date-range');
            });
            initAttendancePicker();
            initAttendanceTooltips();
        </script>
    @endscript
</div>
