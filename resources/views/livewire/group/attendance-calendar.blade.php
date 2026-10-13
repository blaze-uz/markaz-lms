<div>
    @push('css')
        <link href="{{ asset('assets/libs/flatpickr/flatpickr.min.css') }}" rel="stylesheet" type="text/css">
        <style>
            /* Sticky columns */
            .attendance-table-sticky th:nth-child(1),
            .attendance-table-sticky th:nth-child(2),
            .attendance-table-sticky td:nth-child(1),
            .attendance-table-sticky td:nth-child(2) {
                position: sticky;
                background-color: #fff;
                z-index: 10;
            }

            .attendance-table-sticky th:nth-child(1),
            .attendance-table-sticky td:nth-child(1) {
                left: 0;
                z-index: 12;
                border-right: 1px solid #dee2e6;
            }

            .attendance-table-sticky th:nth-child(2),
            .attendance-table-sticky td:nth-child(2) {
                left: 66px;
                z-index: 11;
                box-shadow: 2px 0 5px rgba(0, 0, 0, 0.05);
            }

            /* Header rows sticky */
            .attendance-table-sticky thead tr:first-child th:nth-child(1),
            .attendance-table-sticky thead tr:first-child th:nth-child(2) {
                z-index: 25;
                background-color: #f8f9fa;
            }

            .attendance-table-sticky thead tr:nth-child(2) th {
                position: sticky;
                top: 0;
                z-index: 15;
                background-color: #f8f9fa;
            }

            .attendance-table-sticky thead tr:first-child th {
                position: sticky;
                top: 0;
                z-index: 20;
            }

            .attendance-table-sticky tbody tr:hover td:nth-child(1),
            .attendance-table-sticky tbody tr:hover td:nth-child(2) {
                background-color: #f8f9fa;
            }

            /* Table styling */
            .attendance-table-sticky {
                border-collapse: separate;
                border-spacing: 0;
            }

            .attendance-table-sticky thead th {
                background-color: #f8f9fa;
                font-weight: 600;
                padding: 12px 10px;
                border: 1px solid #dee2e6;
                color: #495057;
            }

            .attendance-table-sticky tbody td {
                padding: 12px 10px;
                vertical-align: middle;
                border: 1px solid #dee2e6;
                background-color: #fff;
            }

            .attendance-table-sticky tbody tr:hover td {
                background-color: #f8f9fa;
            }

            .attendance-table-sticky .attendance-detail-trigger {
                cursor: pointer;
            }

            /* Hide mobile view on desktop */
            .mobile-view {
                display: none;
            }

            /* Mobile responsive */
            @media (max-width: 768px) {
                .table-responsive {
                    overflow-x: auto;
                    -webkit-overflow-scrolling: touch;
                }

                /* Hide table on mobile */
                .desktop-table {
                    display: none !important;
                }

                /* Show mobile view */
                .mobile-view {
                    display: block;
                }

                /* Mobile card view */
                .mobile-attendance-card {
                    border: 1px solid #dee2e6;
                    border-radius: 8px;
                    margin-bottom: 12px;
                    background: #fff;
                    overflow: hidden;
                }

                .mobile-attendance-card .student-header {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    padding: 15px;
                    background: #f8f9fa;
                    cursor: pointer;
                    transition: background-color 0.2s;
                }

                .mobile-attendance-card .student-header:hover {
                    background: #e9ecef;
                }

                .mobile-attendance-card .student-name {
                    font-weight: 600;
                    font-size: 1rem;
                    margin: 0;
                }

                .mobile-attendance-card .collapse-icon {
                    transition: transform 0.3s;
                }

                .mobile-attendance-card .collapse-icon.rotate {
                    transform: rotate(180deg);
                }

                .mobile-attendance-card .attendance-content {
                    max-height: 60vh;
                    overflow-y: auto;
                    padding: 0 15px;
                }

                .mobile-attendance-card .attendance-item {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    padding: 12px 0;
                    border-bottom: 1px solid #f8f9fa;
                }

                .mobile-attendance-card .attendance-item:last-child {
                    border-bottom: none;
                }

                .mobile-attendance-card .date-label {
                    font-weight: 500;
                    color: #495057;
                }

                .mobile-attendance-card .attendance-values {
                    display: flex;
                    gap: 10px;
                    align-items: center;
                }

                .mobile-attendance-card .attendance-item.attendance-detail-trigger {
                    cursor: pointer;
                }

                .mobile-attendance-card .attendance-content::-webkit-scrollbar {
                    width: 6px;
                }

                .mobile-attendance-card .attendance-content::-webkit-scrollbar-track {
                    background: #f1f1f1;
                }

                .mobile-attendance-card .attendance-content::-webkit-scrollbar-thumb {
                    background: #888;
                    border-radius: 3px;
                }

                .mobile-attendance-card .attendance-content::-webkit-scrollbar-thumb:hover {
                    background: #555;
                }
            }

            /* Touch improvements */
            @media (hover: none) and (pointer: coarse) {
                .attendance-table-sticky tbody td {
                    padding: 15px 10px;
                }
            }
        </style>
    @endpush

    <!-- Main Section -->
    <div class="row">
        <!-- Left Side: Header + Table -->
        <div class="col-lg-4">
            <div class="card rounded-4 p-2">
                <div class="card-header">
                    <i class="ri-calendar-line me-1"></i>
                    {{ $group->name }}
                </div>
                <div class="card-body p-0">
                    <div wire:ignore>
                        <input type="text" class="form-control d-none" id="attendance-calendar"
                            data-date-format="d M, Y" data-inline-date="true">
                        <input type="hidden" id="attendance-start-date" value="{{ $startDate }}">
                        <input type="hidden" id="attendance-end-date" value="{{ $endDate }}">
                    </div>
                </div>
            </div>
        </div>
        <!-- Calendar - Right Side -->
        <div class="col-lg-8">
            <!-- Header -->
            <div class="card rounded-4 mb-3">
                <div class="card-body d-flex align-items-center gap-3 flex-wrap">
                    <div class="d-flex align-items-center gap-2">
                        <i class="ri-calendar-check-line text-primary fs-4"></i>
                        <h5 class="mb-0">@lang('translation.groups.attendance')</h5>
                    </div>
                    <div class="vr d-none d-md-block"></div>
                    <div class="d-flex gap-3 flex-wrap">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fa-regular fa-circle-check text-success"></i>
                            <small>@lang('attr.present')</small>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <i class="fa-regular fa-circle-xmark text-danger"></i>
                            <small>@lang('attr.absent')</small>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-primary">10</span>
                            <small>@lang('translation.students.score')</small>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <i class="ri-chat-3-line text-info"></i>
                            <small>@lang('translation.students.comment')</small>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <i class="ri-survey-line text-secondary"></i>
                            <small>@lang('translation.groups.lesson_theme')</small>
                        </div>
                        @if($hasHomework)
                        <div class="d-flex align-items-center gap-2">
                            <i class="ri-book-line text-warning"></i>
                            <small>@lang('translation.groups.homework')</small>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="card rounded-4">
                <div class="card-body">
                    <div class="table-responsive">
                        @if (!count($students))
                            <x-no-result />
                        @else
                            <!-- Desktop Table View -->
                            <table class="table table-bordered attendance-table-sticky desktop-table">
                                <thead>
                                    <tr>
                                        <th rowspan="2" class="align-middle" style="min-width: 66px;">#</th>
                                        <th rowspan="2" class="align-middle" style="min-width: 200px;">
                                            @lang('translation.students.full_name')</th>
                                        @foreach ($uniqueDays as $day)
                                            <th colspan="{{ ($hasHomework ? 4 : 3) + 1 }}" class="text-center">{{ dateFormat($day, 'd-F') }}</th>
                                        @endforeach
                                    </tr>
                                    <tr>
                                        @foreach ($uniqueDays as $day)
                                            <th class="text-center" style="width: 60px;">@lang('translation.status')</th>
                                            <th class="text-center" style="width: 50px;">@lang('translation.students.score')</th>
                                            <th class="text-center" style="width: 50px;">@lang('translation.students.comment')</th>
                                            <th class="text-center" style="width: 50px;">@lang('translation.groups.lesson_theme')</th>
                                            @if($hasHomework)
                                            <th class="text-center" style="width: 50px;">@lang('translation.groups.homework')</th>
                                            @endif
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($students as $student)
                                        <tr>
                                            <td>{{ $loop->index + 1 }}</td>
                                            <td class="text-nowrap">
                                                <a href="{{ route('admin.students.show', $student->id) }}"
                                                    class="py-3 text-reset">
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
                                                    $attendance = $attendanceData
                                                        ->where('student_id', $student->id)
                                                        ->where('date', $day)
                                                        ->first();
                                                    $detailPayload = [
                                                        'date' => $day,
                                                        'dateFormatted' => dateFormat($day, 'd-F'),
                                                        'studentName' => $student->getFullName(),
                                                        'status' => $attendance?->status?->value,
                                                        'score' => $attendance?->score,
                                                        'comment' => $attendance?->comment,
                                                        'theme' => $attendance?->theme,
                                                        'homework' => $attendance && $attendance->homework,
                                                    ];
                                                @endphp
                                                <td class="text-center attendance-detail-trigger" role="button" tabindex="0"
                                                    data-attendance="{{ base64_encode(json_encode($detailPayload)) }}">
                                                    @if ($attendance?->status)
                                                        @if ($attendance->status->value == 'present')
                                                            <i class="fa-regular fa-circle-check text-success fs-5"></i>
                                                        @else
                                                            <i class="fa-regular fa-circle-xmark text-danger fs-5"></i>
                                                        @endif
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td class="text-center attendance-detail-trigger" role="button" tabindex="0"
                                                    data-attendance="{{ base64_encode(json_encode($detailPayload)) }}">
                                                    @if ($attendance?->score)
                                                        <span class="badge bg-primary">{{ $attendance->score }}</span>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td class="text-center attendance-detail-trigger" role="button" tabindex="0"
                                                    data-attendance="{{ base64_encode(json_encode($detailPayload)) }}">
                                                    @if ($attendance?->comment)
                                                        <i class="ri-chat-3-line text-info fs-5"></i>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td class="text-center attendance-detail-trigger" role="button" tabindex="0"
                                                    data-attendance="{{ base64_encode(json_encode($detailPayload)) }}">
                                                    @if ($attendance?->theme)
                                                        <i class="ri-survey-line text-secondary fs-5" data-bs-toggle="tooltip" title="{{ $attendance->theme }}"></i>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                @if($hasHomework)
                                                <td class="text-center attendance-detail-trigger" role="button" tabindex="0"
                                                    data-attendance="{{ base64_encode(json_encode($detailPayload)) }}">
                                                    @if ($attendance && $attendance->homework)
                                                        <i class="ri-checkbox-circle-fill text-success fs-5"></i>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                @endif
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            <!-- Mobile Card View -->
                            <div class="mobile-view">
                                @foreach ($students as $student)
                                    <div class="mobile-attendance-card">
                                        <div class="student-header" data-bs-toggle="collapse"
                                            data-bs-target="#student-{{ $student->id }}" aria-expanded="false">
                                            <div class="student-name">
                                                {{ $loop->index + 1 }}.
                                                @if ($student->trashed())
                                                    <del>{{ $student->getFullName() }}</del>
                                                @else
                                                    {{ $student->getFullName() }}
                                                @endif
                                            </div>
                                            <i class="ri-arrow-down-s-line fs-4 collapse-icon"
                                                id="icon-{{ $student->id }}"></i>
                                        </div>
                                        <div class="collapse" id="student-{{ $student->id }}">
                                            <div class="attendance-content">
                                                @foreach ($uniqueDays as $day)
                                                    @php
                                                        $attendance = $attendanceData
                                                            ->where('student_id', $student->id)
                                                            ->where('date', $day)
                                                            ->first();
                                                        $detailPayload = [
                                                            'date' => $day,
                                                            'dateFormatted' => dateFormat($day, 'd-F'),
                                                            'studentName' => $student->getFullName(),
                                                            'status' => $attendance?->status?->value,
                                                            'score' => $attendance?->score,
                                                            'comment' => $attendance?->comment,
                                                            'homework' => $attendance && $attendance->homework,
                                                        ];
                                                    @endphp
                                                    <div class="attendance-item attendance-detail-trigger" role="button" tabindex="0"
                                                        data-attendance="{{ base64_encode(json_encode($detailPayload)) }}">
                                                        <span class="date-label">{{ dateFormat($day, 'd-F') }}</span>
                                                        <div class="attendance-values">
                                                            @if ($attendance?->status)
                                                                @if ($attendance->status->value == 'present')
                                                                    <i class="fa-regular fa-circle-check text-success fs-5"></i>
                                                                @else
                                                                    <i class="fa-regular fa-circle-xmark text-danger fs-5"></i>
                                                                @endif
                                                            @else
                                                                <span class="text-muted">-</span>
                                                            @endif

                                                            @if ($attendance?->score)
                                                                <span class="badge bg-primary">{{ $attendance->score }}</span>
                                                            @endif

                                                            @if ($attendance?->comment)
                                                                <i class="ri-chat-3-line text-info fs-5"></i>
                                                            @endif
 
                                                            @if ($attendance?->theme)
                                                                <i class="ri-survey-line text-secondary fs-5"></i>
                                                            @endif

                                                            @if($hasHomework && $attendance && $attendance->homework)
                                                                <i class="ri-checkbox-circle-fill text-success fs-5"></i>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
                <div class="card-preloader d-none" wire:loading.class.remove="d-none">
                    <div class="card-status">
                        <div class="spinner-border text-success">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Attendance detail modal -->
    <div wire:ignore class="modal fade" id="attendanceDetailModal" tabindex="-1" aria-labelledby="attendanceDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4">
                <div class="modal-header">
                    <h5 class="modal-title" id="attendanceDetailModalLabel">
                        <i class="ri-calendar-check-line me-2"></i>@lang('translation.groups.attendance')
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="attendanceDetailModalBody">
                        <p class="mb-2"><strong>@lang('translation.students.full_name'):</strong> <span id="modal-student-name"></span></p>
                        <p class="mb-2"><strong>@lang('translation.date'):</strong> <span id="modal-date"></span></p>
                        <p class="mb-2"><strong>@lang('translation.status'):</strong> <span id="modal-status"></span></p>
                        <p class="mb-2"><strong>@lang('translation.students.score'):</strong> <span id="modal-score"></span></p>
                        <p class="mb-2"><strong>@lang('translation.students.comment'):</strong> <span id="modal-comment"></span></p>
                        <p class="mb-2"><strong>@lang('translation.groups.lesson_theme'):</strong> <span id="modal-theme"></span></p>
                        <p class="mb-0"><strong>@lang('translation.groups.homework'):</strong> <span id="modal-homework"></span></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('script-bottom')
        <script src="{{ asset('assets/libs/flatpickr/flatpickr.min.js') }}"></script>
    @endpush

    @script
    <script>

    
        const statusLabels = {
            present: @js(__('attr.present')),
            absent: @js(__('attr.absent'))
        };

        $wire.on('$refresh', () => {
            initAttendanceCalendar();
        });

        function initAttendanceCalendar() {
            setTimeout(() => {
                const calendarEl = document.getElementById('attendance-calendar');
                const startDateEl = document.getElementById('attendance-start-date');
                const endDateEl = document.getElementById('attendance-end-date');

                if (!calendarEl || !startDateEl || !endDateEl) return;

                if (calendarEl._flatpickr) {
                    calendarEl._flatpickr.destroy();
                }

                let startDate = startDateEl.value;
                let endDate = endDateEl.value;

                flatpickr(calendarEl, {
                    inline: true,
                    mode: "range",
                    enableTime: false,
                    dateFormat: "Y-m-d",
                    defaultDate: [startDate, endDate],
                    time_24hr: true,
                    locale: {
                        firstDayOfWeek: 1,
                        weekdays: {
                            shorthand: ['Yak', 'Du', 'Se', 'Chor', 'Pay', 'Ju', 'Sha'],
                            longhand: ['Yakshanba', 'Dushanba', 'Seshanba', 'Chorshanba', 'Payshanba',
                                'Juma', 'Shanba'
                            ],
                        },
                        months: {
                            shorthand: ['Yan', 'Fev', 'Mar', 'Apr', 'May', 'Iyun', 'Iyul', 'Avg', 'Sen',
                                'Okt', 'Noy', 'Dek'
                            ],
                            longhand: ['Yanvar', 'Fevral', 'Mart', 'Aprel', 'May', 'Iyun', 'Iyul',
                                'Avgust', 'Sentabr', 'Oktabr', 'Noyabr', 'Dekabr'
                            ],
                        },
                    },
                    onChange: function(selectedDates, dateStr, instance) {
                        if (selectedDates.length === 2) {
                            const start = selectedDates[0];
                            const end = selectedDates[1];

                            const startFormatted = start.toISOString().split('T')[0];
                            const endFormatted = end.toISOString().split('T')[0];

                            document.getElementById('attendance-start-date').value = startFormatted;
                            document.getElementById('attendance-end-date').value = endFormatted;

                            $wire.dispatch('calendar-range-changed', {
                                startDate: startFormatted,
                                endDate: endFormatted
                            });
                        }
                    }
                });
            }, 100);
        }

        initAttendanceCalendar();

        function openAttendanceModal(trigger) {
            const data = trigger.getAttribute('data-attendance');
            if (!data) return;
            try {
                // Decode base64 and parse JSON
                const decodedData = atob(data);
                const payload = JSON.parse(decodedData);
                const modal = document.getElementById('attendanceDetailModal');
                if (!modal) return;
                
                document.getElementById('modal-student-name').textContent = payload.studentName || '—';
                document.getElementById('modal-date').textContent = payload.dateFormatted || payload.date || '—';
                document.getElementById('modal-status').innerHTML = payload.status === 'present'
                    ? '<i class="fa-regular fa-circle-check text-success"></i> ' + (statusLabels.present || payload.status)
                    : payload.status === 'absent'
                        ? '<i class="fa-regular fa-circle-xmark text-danger"></i> ' + (statusLabels.absent || payload.status)
                        : '—';
                document.getElementById('modal-score').innerHTML = payload.score
                    ? '<span class="badge bg-primary">' + payload.score + '</span>'
                    : '—';
                document.getElementById('modal-comment').textContent = payload.comment || '—';
                document.getElementById('modal-theme').textContent = payload.theme || '—';
                document.getElementById('modal-homework').innerHTML = payload.homework
                    ? '<i class="ri-checkbox-circle-fill text-success"></i>'
                    : '—';
                
                let modalInstance;
                if (window.bootstrap && bootstrap.Modal) {
                    const existingInstance = bootstrap.Modal.getInstance(modal);
                    modalInstance = existingInstance || new bootstrap.Modal(modal);
                    modalInstance.show();
                } else {
                    $(modal).modal('show');
                }
            } catch (err) {
                console.error('Error opening attendance modal:', err);
            }
        }

        document.addEventListener('click', function(e) {
            const trigger = e.target.closest('.attendance-detail-trigger');
            if (trigger) {
                e.preventDefault();
                e.stopPropagation();
                openAttendanceModal(trigger);
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key !== 'Enter') return;
            const trigger = e.target.closest('.attendance-detail-trigger');
            if (trigger) {
                e.preventDefault();
                openAttendanceModal(trigger);
            }
        });

        document.addEventListener('shown.bs.collapse', function(event) {
            const targetId = event.target.id;
            const iconId = 'icon-' + targetId.replace('student-', '');
            const icon = document.getElementById(iconId);
            if (icon) {
                icon.classList.add('rotate');
            }
        });

        document.addEventListener('hidden.bs.collapse', function(event) {
            const targetId = event.target.id;
            const iconId = 'icon-' + targetId.replace('student-', '');
            const icon = document.getElementById(iconId);
            if (icon) {
                icon.classList.remove('rotate');
            }
        });
        </script>
    @endscript
</div>
