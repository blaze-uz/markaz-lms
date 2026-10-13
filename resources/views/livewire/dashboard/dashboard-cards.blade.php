<div class="row g-2 g-md-3">
    <!-- Students Card -->
    <div class="col-6 col-md-6 col-xl-3">
        <a href="{{ route('admin.students.index') }}" class="text-decoration-none">
            <div class="card dashboard-card h-100">
                <div class="card-body p-3 p-md-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="flex-grow-1">
                            <p class="mb-1 mb-md-2 fw-medium text-muted fs-6 fs-md-5">@lang('translation.students.count')</p>
                            <h3 class="mb-0 fw-bold text-dark fs-4 fs-md-3">
                                <span class="counter-value" data-target="{{ $studentsCount }}">0</span>
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
        </a>
    </div>

    <!-- Groups Card -->
    <div class="col-6 col-md-6 col-xl-3">
        <a href="{{ route('admin.groups.index') }}" class="text-decoration-none">
            <div class="card dashboard-card h-100">
                <div class="card-body p-3 p-md-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="flex-grow-1">
                            <p class="mb-1 mb-md-2 fw-medium text-muted fs-6 fs-md-5">@lang('translation.groups.groups_count')</p>
                            <h3 class="mb-0 fw-bold text-dark fs-4 fs-md-3">
                                <span>{{ $groupsCount }}</span>
                            </h3>
                        </div>
                        <div class="flex-shrink-0">
                            <div class="dashboard-icon">
                                <span class="mdi mdi-layers-triple-outline"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <!-- Teachers Card -->
    <div class="col-6 col-md-6 col-xl-3">
        <a href="{{ route('admin.users.index') }}" class="text-decoration-none">
            <div class="card dashboard-card h-100">
                <div class="card-body p-3 p-md-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="flex-grow-1">
                            <p class="mb-1 mb-md-2 fw-medium text-muted fs-6 fs-md-5">@lang('translation.users.staff_count')</p>
                            <h3 class="mb-0 fw-bold text-dark fs-4 fs-md-3">
                                <span>{{ $teachersCount }}</span>
                            </h3>
                        </div>
                        <div class="flex-shrink-0">
                            <div class="dashboard-icon">
                                <span class="mdi mdi-school-outline"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <!-- Courses Card -->
    <div class="col-6 col-md-6 col-xl-3">
        <a href="{{ route('admin.courses.index') }}" class="text-decoration-none">
            <div class="card dashboard-card h-100">
                <div class="card-body p-3 p-md-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="flex-grow-1">
                            <p class="mb-1 mb-md-2 fw-medium text-muted fs-6 fs-md-5">@lang('translation.courses.courses_count')</p>
                            <h3 class="mb-0 fw-bold text-dark fs-4 fs-md-3">
                                <span>{{ $coursesCount }}</span>
                            </h3>
                        </div>
                        <div class="flex-shrink-0">
                            <div class="dashboard-icon">
                                <span class="mdi mdi-folder-multiple-outline"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>
@push('css')
    <style>
        .row .col-6 .dashboard-card,
        .row .col-md-6 .dashboard-card,
        .row .col-xl-3 .dashboard-card {
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
        .row .col-xl-3 .dashboard-card::before {
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
        .row .col-xl-3 .dashboard-card:hover {
            transform: translateY(-8px) !important;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12) !important;
            border-color: rgba(99, 102, 241, 0.2) !important;
        }

        .row .col-6 .dashboard-card:hover::before,
        .row .col-md-6 .dashboard-card:hover::before,
        .row .col-xl-3 .dashboard-card:hover::before {
            opacity: 1 !important;
        }

        .row .col-6 .dashboard-icon,
        .row .col-md-6 .dashboard-icon,
        .row .col-xl-3 .dashboard-icon {
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
        .row .col-xl-3 .dashboard-card:hover .dashboard-icon {
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
        .row .col-xl-3:nth-child(1) .dashboard-icon {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%) !important;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3) !important;
        }

        .row .col-6:nth-child(1) .dashboard-card:hover .dashboard-icon,
        .row .col-md-6:nth-child(1) .dashboard-card:hover .dashboard-icon,
        .row .col-xl-3:nth-child(1) .dashboard-card:hover .dashboard-icon {
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.4) !important;
        }

        .row .col-6:nth-child(2) .dashboard-icon,
        .row .col-md-6:nth-child(2) .dashboard-icon,
        .row .col-xl-3:nth-child(2) .dashboard-icon {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3) !important;
        }

        .row .col-6:nth-child(2) .dashboard-card:hover .dashboard-icon,
        .row .col-md-6:nth-child(2) .dashboard-card:hover .dashboard-icon,
        .row .col-xl-3:nth-child(2) .dashboard-card:hover .dashboard-icon {
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4) !important;
        }

        .row .col-6:nth-child(3) .dashboard-icon,
        .row .col-md-6:nth-child(3) .dashboard-icon,
        .row .col-xl-3:nth-child(3) .dashboard-icon {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important;
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3) !important;
        }

        .row .col-6:nth-child(3) .dashboard-card:hover .dashboard-icon,
        .row .col-md-6:nth-child(3) .dashboard-card:hover .dashboard-icon,
        .row .col-xl-3:nth-child(3) .dashboard-card:hover .dashboard-icon {
            box-shadow: 0 8px 25px rgba(245, 158, 11, 0.4) !important;
        }

        .row .col-6:nth-child(4) .dashboard-icon,
        .row .col-md-6:nth-child(4) .dashboard-icon,
        .row .col-xl-3:nth-child(4) .dashboard-icon {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%) !important;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3) !important;
        }

        .row .col-6:nth-child(4) .dashboard-card:hover .dashboard-icon,
        .row .col-md-6:nth-child(4) .dashboard-card:hover .dashboard-icon,
        .row .col-xl-3:nth-child(4) .dashboard-card:hover .dashboard-icon {
            box-shadow: 0 8px 25px rgba(239, 68, 68, 0.4) !important;
        }

        /* Mobile optimizations */
        @media (max-width: 767.98px) {
            .dashboard-card {
                border-radius: 16px !important;
                box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            }

            .dashboard-card:hover {
                transform: translateY(-4px);
                box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
            }

            .dashboard-icon {
                width: 40px;
                height: 40px;
                font-size: 16px;
                border-radius: 12px !important;
            }

            .dashboard-card:hover .dashboard-icon {
                transform: scale(1.1) rotate(3deg);
            }

            .card-body {
                padding: 1rem !important;
            }
        }

        /* Tablet optimizations */
        @media (min-width: 768px) and (max-width: 1199.98px) {
            .dashboard-card {
                border-radius: 18px !important;
            }

            .dashboard-card:hover {
                transform: translateY(-6px);
            }

            .dashboard-icon {
                width: 44px;
                height: 44px;
                font-size: 18px;
                border-radius: 14px !important;
            }
        }

        /* Add loading animation for counter */
        @keyframes countUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .counter-value {
            animation: countUp 0.6s ease-out;
        }

        /* Subtle pulse effect on hover */
        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.8;
            }
        }

        .dashboard-card:hover::before {
            animation: pulse 2s infinite;
        }
    </style>
@endpush
