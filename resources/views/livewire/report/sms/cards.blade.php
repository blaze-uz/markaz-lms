<div class="row g-2">
    <!-- Total SMS Card -->
    <div class="col-xl col-lg-6 col-md-6">
        <div class="card card-animate rounded-4">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-1 fw-medium text-muted small">@lang('translation.sms.total')</p>
                        <h4 class="mb-1 ff-secondary fw-semibold">
                            {{ $totalSms }}
                        </h4>
                        <small class="text-muted">@lang('translation.dashboards.total_sms')</small>
                    </div>
                    <div>
                        <div class="flex-shrink-0 avatar-sm">
                            <span class="avatar-title bg-primary-subtle rounded-circle">
                                <i class="fas fa-sms text-primary"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sent SMS Card -->
    <div class="col-xl col-lg-6 col-md-6">
        <div class="card card-animate rounded-4">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-1 fw-medium text-muted small">@lang('translation.sms.sent')</p>
                        <h4 class="mb-1 ff-secondary fw-semibold">
                            {{ $sentSms }}
                        </h4>
                        <small class="text-muted" style="cursor: pointer;" title="@lang('translation.dashboards.success_rate')">
                            {{ $successRate }}% @lang('translation.dashboards.success_rate')
                        </small>
                    </div>
                    <div>
                        <div class="flex-shrink-0 avatar-sm">
                            <span class="avatar-title bg-success-subtle rounded-circle">
                                <i class="fas fa-check-circle text-success"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Failed SMS Card -->
    <div class="col-xl col-lg-6 col-md-6">
        <div class="card card-animate rounded-4">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-1 fw-medium text-muted small">@lang('translation.sms.failed')</p>
                        <h4 class="mb-1 ff-secondary fw-semibold">
                            {{ $failedSms }}
                        </h4>
                        <small class="text-muted" style="cursor: pointer;" title="@lang('translation.dashboards.error_rate')">
                            {{ $failedRate }}% @lang('translation.dashboards.error_rate')
                        </small>
                    </div>
                    <div>
                        <div class="flex-shrink-0 avatar-sm">
                            <span class="avatar-title bg-danger-subtle rounded-circle">
                                <i class="fas fa-times-circle text-danger"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cost Analysis Card -->
    <div class="col-xl col-lg-6 col-md-6">
        <div class="card card-animate rounded-4">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-1 fw-medium text-muted small">@lang('translation.dashboards.cost_analysis')</p>
                        <h4 class="mb-1 ff-secondary fw-semibold">
                            {{ number_format($actualCost) }} UZS
                        </h4>
                        <small class="text-muted">@lang('translation.dashboards.actual_cost')</small>
                    </div>
                    <div>
                        <div class="flex-shrink-0 avatar-sm">
                            <span class="avatar-title bg-secondary-subtle rounded-circle">
                                <i class="fas fa-calculator text-secondary"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Second Row with Additional Metrics -->
<div class="row g-2 mt-2">
    <!-- Pending SMS Card -->
    <div class="col-xl col-lg-6 col-md-6">
        <div class="card card-animate rounded-4">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-1 fw-medium text-muted small">@lang('translation.sms.pending')</p>
                        <h4 class="mb-1 ff-secondary fw-semibold">
                            {{ $pendingSms }}
                        </h4>
                        <small class="text-muted">@lang('translation.sms.waiting')</small>
                    </div>
                    <div>
                        <div class="flex-shrink-0 avatar-sm">
                            <span class="avatar-title bg-warning-subtle rounded-circle">
                                <i class="fas fa-clock text-warning"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scheduled SMS Card -->
    <div class="col-xl col-lg-6 col-md-6">
        <div class="card card-animate rounded-4">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-1 fw-medium text-muted small">@lang('translation.sms.scheduled')</p>
                        <h4 class="mb-1 ff-secondary fw-semibold">
                            {{ $scheduledSms }}
                        </h4>
                        <small class="text-muted">@lang('translation.sms.scheduled')</small>
                    </div>
                    <div>
                        <div class="flex-shrink-0 avatar-sm">
                            <span class="avatar-title bg-info-subtle rounded-circle">
                                <i class="fas fa-calendar text-info"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Average Cost per SMS -->
    <div class="col-xl col-lg-6 col-md-6">
        <div class="card card-animate rounded-4">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-1 fw-medium text-muted small">O'rtacha xarajat</p>
                        <h4 class="mb-1 ff-secondary fw-semibold">
                            {{ $averageCostPerSms }} UZS
                        </h4>
                        <small class="text-muted">SMS boshiga</small>
                    </div>
                    <div>
                        <div class="flex-shrink-0 avatar-sm">
                            <span class="avatar-title bg-purple-subtle rounded-circle">
                                <i class="fas fa-chart-line text-purple"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Daily Average -->
    <div class="col-xl col-lg-6 col-md-6">
        <div class="card card-animate rounded-4">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-1 fw-medium text-muted small">Kunlik o'rtacha</p>
                        <h4 class="mb-1 ff-secondary fw-semibold">
                            {{ $dailyAverage }}
                        </h4>
                        <small class="text-muted">SMS kuniga</small>
                    </div>
                    <div>
                        <div class="flex-shrink-0 avatar-sm">
                            <span class="avatar-title bg-teal-subtle rounded-circle">
                                <i class="fas fa-calendar-day text-teal"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Efficiency Score -->
    <div class="col-xl col-lg-6 col-md-6">
        <div class="card card-animate rounded-4">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-1 fw-medium text-muted small">Samaradorlik</p>
                        <h4 class="mb-1 ff-secondary fw-semibold">
                            {{ $efficiencyScore }}%
                        </h4>
                        <small class="text-muted">Umumiy ko'rsatkich</small>
                    </div>
                    <div>
                        <div class="flex-shrink-0 avatar-sm">
                            <span class="avatar-title bg-orange-subtle rounded-circle">
                                <i class="fas fa-trophy text-orange"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('css')
    <style>
        .bg-purple-subtle {
            background-color: rgba(139, 92, 246, 0.1) !important;
        }

        .text-purple {
            color: #8b5cf6 !important;
        }

        .bg-teal-subtle {
            background-color: rgba(20, 184, 166, 0.1) !important;
        }

        .text-teal {
            color: #14b8a6 !important;
        }

        .bg-orange-subtle {
            background-color: rgba(249, 115, 22, 0.1) !important;
        }

        .text-orange {
            color: #f97316 !important;
        }
    </style>
@endpush
