<div class="row g-2 g-md-3 mb-3">
    @if ($item->state == 'demo')
        <div class="col-6 col-md-6 col-xl-4">
            <a href="{{ route('admin.groups.start-group-view', $item->id) }}" class="text-decoration-none">
                <div class="card dashboard-card mb-1">
                    <div class="card-body p-3 p-md-4">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="flex-grow-1">
                                <p class="mb-1 mb-md-2 fw-medium text-muted fs-6 fs-md-5">@lang('translation.groups.start_lessons')</p>
                                <h3 class="mb-0 fw-bold text-dark fs-4 fs-md-3">
                                    <span class="btn btn-success btn-sm">
                                        <i class="ri-arrow-right-line me-1"></i>
                                        @lang('translation.groups.start')
                                    </span>
                                </h3>
                            </div>
                            <div class="flex-shrink-0">
                                <div class="dashboard-icon">
                                    <i class="mdi mdi-clock-time-eight-outline"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-6 col-md-6 col-xl-4">
            <div class="card dashboard-card mb-1">
                <div class="card-body p-3 p-md-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="flex-grow-1">
                            <p class="mb-1 mb-md-2 fw-medium text-muted fs-6 fs-md-5">@lang('translation.groups.info')</p>
                            <p class="mb-0 text-muted small">
                                @lang('translation.groups.demo_group_info')
                            </p>
                        </div>
                        <div class="flex-shrink-0">
                            <div class="dashboard-icon">
                                <i class="mdi mdi-alert-outline"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="col-6 col-md-6 col-xl-4">
            <div class="card dashboard-card mb-1">
                <div class="card-body p-3 p-md-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="flex-grow-1">
                            <p class="mb-1 mb-md-2 fw-medium text-muted fs-6 fs-md-5">@lang('translation.students.count')</p>
                            <h3 class="mb-0 fw-bold text-dark fs-4 fs-md-3">
                                <span class="counter-value" data-target="{{ $item->students->count() }}">0</span>
                            </h3>
                        </div>
                        <div class="flex-shrink-0">
                            <div class="dashboard-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round" class="feather feather-users">
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="9" cy="7" r="4"></circle>
                                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if ($item->state !== 'completed')
            <div class="col-6 col-md-6 col-xl-4">
                <div class="card dashboard-card mb-1">
                    <div class="card-body p-3 p-md-4">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="flex-grow-1">
                                <p class="mb-1 mb-md-2 fw-medium text-muted fs-6 fs-md-5">@lang('translation.students.debt_students')</p>
                                <h3 class="mb-0 fw-bold text-dark fs-4 fs-md-3">
                                    <span class="counter-value" data-target="{{ $debtStudentsCount }}">0</span>
                                </h3>
                            </div>
                            <div class="flex-shrink-0">
                                <div class="dashboard-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round" class="feather feather-users">
                                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="9" cy="7" r="4"></circle>
                                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-6 col-xl-4">
                <div class="card dashboard-card mb-1">
                    <div class="card-body p-3 p-md-4">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="flex-grow-1">
                                <p class="mb-1 mb-md-2 fw-medium text-muted fs-6 fs-md-5">@lang('translation.groups.today_lesson_time')</p>
                                <h3 class="mb-0 fw-bold text-dark fs-4 fs-md-3">
                                    <span>{{ $item->groupTimes->where('name', strtolower(now()->format('l')))->first()->start ?? '-' }}</span>
                                </h3>
                            </div>
                            <div class="flex-shrink-0">
                                <div class="dashboard-icon">
                                    <i class="mdi mdi-clock-time-eight-outline"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>

@push('css')
    <style>
        .row .col-6 .dashboard-card,
        .row .col-md-6 .dashboard-card,
        .row .col-xl-4 .dashboard-card {
            border: none !important;
            border-radius: 20px !important;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08) !important;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1) !important;
            background: #ffffff !important;
            position: relative !important;
            overflow: hidden !important;
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
        }

        .row .col-6 .dashboard-card::before,
        .row .col-md-6 .dashboard-card::before,
        .row .col-xl-4 .dashboard-card::before {
            content: '' !important;
            position: absolute !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            bottom: 0 !important;
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.05) 0%, rgba(168, 85, 247, 0.05) 100%) !important;
            opacity: 0 !important;
            transition: opacity 0.4s ease !important;
        }

        .row .col-6 .dashboard-card:hover,
        .row .col-md-6 .dashboard-card:hover,
        .row .col-xl-4 .dashboard-card:hover {
            transform: translateY(-8px) !important;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12) !important;
            border-color: rgba(99, 102, 241, 0.2) !important;
        }

        .row .col-6 .dashboard-card:hover::before,
        .row .col-md-6 .dashboard-card:hover::before,
        .row .col-xl-4 .dashboard-card:hover::before {
            opacity: 1 !important;
        }

        .row .col-6 .dashboard-icon,
        .row .col-md-6 .dashboard-icon,
        .row .col-xl-4 .dashboard-icon {
            width: 48px !important;
            height: 48px !important;
            border-radius: 16px !important;
            background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%) !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            color: white !important;
            font-size: 20px !important;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1) !important;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3) !important;
        }

        .row .col-6 .dashboard-card:hover .dashboard-icon,
        .row .col-md-6 .dashboard-card:hover .dashboard-icon,
        .row .col-xl-4 .dashboard-card:hover .dashboard-icon {
            transform: scale(1.15) rotate(5deg) !important;
            box-shadow: 0 8px 25px rgba(99, 102, 241, 0.4) !important;
        }

        /* Enhanced text styling */
        .dashboard-card .card-body p {
            color: #64748b !important;
            font-weight: 500;
            letter-spacing: 0.025em;
        }

        .dashboard-card .card-body h3 {
            color: #1e293b !important;
            font-weight: 700;
            letter-spacing: -0.025em;
        }

        /* Add subtle animation to numbers */
        .counter-value {
            display: inline-block;
            transition: all 0.3s ease;
        }

        .dashboard-card:hover .counter-value {
            transform: scale(1.05);
        }

        /* Different color themes for each card */
        .row .col-6:nth-child(1) .dashboard-icon,
        .row .col-md-6:nth-child(1) .dashboard-icon,
        .row .col-xl-4:nth-child(1) .dashboard-icon {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%) !important;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3) !important;
        }

        .row .col-6:nth-child(1) .dashboard-card:hover .dashboard-icon,
        .row .col-md-6:nth-child(1) .dashboard-card:hover .dashboard-icon,
        .row .col-xl-4:nth-child(1) .dashboard-card:hover .dashboard-icon {
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.4) !important;
        }

        .row .col-6:nth-child(2) .dashboard-icon,
        .row .col-md-6:nth-child(2) .dashboard-icon,
        .row .col-xl-4:nth-child(2) .dashboard-icon {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important;
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3) !important;
        }

        .row .col-6:nth-child(2) .dashboard-card:hover .dashboard-icon,
        .row .col-md-6:nth-child(2) .dashboard-card:hover .dashboard-icon,
        .row .col-xl-4:nth-child(2) .dashboard-card:hover .dashboard-icon {
            box-shadow: 0 8px 25px rgba(245, 158, 11, 0.4) !important;
        }

        .row .col-6:nth-child(3) .dashboard-icon,
        .row .col-md-6:nth-child(3) .dashboard-icon,
        .row .col-xl-4:nth-child(3) .dashboard-icon {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3) !important;
        }

        .row .col-6:nth-child(3) .dashboard-card:hover .dashboard-icon,
        .row .col-md-6:nth-child(3) .dashboard-card:hover .dashboard-icon,
        .row .col-xl-4:nth-child(3) .dashboard-card:hover .dashboard-icon {
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4) !important;
        }
    </style>
@endpush
