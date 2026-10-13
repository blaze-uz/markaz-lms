<!-- Financial Cards -->
<div class="row g-3 mb-4">
    <!-- KIRIM Card -->
    <div class="col-lg-3 col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center">
                <div class="avatar-sm flex-shrink-0">
                    <span class="avatar-title bg-success-subtle text-success rounded fs-3">
                        <i class="fas fa-arrow-down"></i>
                    </span>
                </div>
                <div class="flex-grow-1 ms-3">
                    <p class="text-uppercase fw-medium fs-12 text-muted mb-1">
                        {{ __('translation.reports.payments') }}
                    </p>
                    <h4 class="mb-0 text-success">{{ money($cardsData['payments']) }}</h4>
                </div>
            </div>
        </div>
    </div>

    <!-- CHIQIM Card -->
    <div class="col-lg-3 col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center">
                <div class="avatar-sm flex-shrink-0">
                    <span class="avatar-title bg-danger-subtle text-danger rounded fs-3">
                        <i class="fas fa-arrow-up"></i>
                    </span>
                </div>
                <div class="flex-grow-1 ms-3">
                    <p class="text-uppercase fw-medium fs-12 text-muted mb-1">
                        {{ __('translation.reports.outgoing') }}
                    </p>
                    <h4 class="mb-0 text-danger">{{ money($cardsData['totalOutgoing']) }}</h4>
                </div>
            </div>
        </div>
    </div>

    <!-- XARAJAT Card -->
    <div class="col-lg-3 col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center">
                <div class="avatar-sm flex-shrink-0">
                    <span class="avatar-title bg-warning-subtle text-warning rounded fs-3">
                        <i class="fas fa-receipt"></i>
                    </span>
                </div>
                <div class="flex-grow-1 ms-3">
                    <p class="text-uppercase fw-medium fs-12 text-muted mb-1">
                        {{ __('translation.reports.expenses') }}
                    </p>
                    <h4 class="mb-0 text-warning">{{ money($cardsData['expenses']) }}</h4>
                </div>
            </div>
        </div>
    </div>

    <!-- MAOSH Card -->
    <div class="col-lg-3 col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center">
                <div class="avatar-sm flex-shrink-0">
                    <span class="avatar-title bg-info-subtle text-info rounded fs-3">
                        <i class="fas fa-user-tie"></i>
                    </span>
                </div>
                <div class="flex-grow-1 ms-3">
                    <p class="text-uppercase fw-medium fs-12 text-muted mb-1">
                        {{ __('translation.withdraw_payments') }}
                    </p>
                    <h4 class="mb-0 text-info">{{ money($cardsData['staffPayments']) }}</h4>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- KASSA Card -->
<div class="row g-3">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center justify-content-center">
                <div class="avatar-md flex-shrink-0">
                    <span class="avatar-title bg-primary-subtle text-primary rounded fs-2">
                        <i class="fas fa-cash-register"></i>
                    </span>
                </div>
                <div class="flex-grow-1 ms-4 text-center">
                    <p class="text-uppercase fw-medium fs-11 text-muted mb-1">
                        {{ __('translation.reports.cash_balance') }}
                    </p>
                    <h2 class="mb-0 text-primary">{{ money($cardsData['cashBalance']) }}</h2>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Account Balance Cards -->
<div class="row g-3 mb-4">
    <!-- Teachers Account -->
    <div class="col-lg-4 col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center">
                <div class="avatar-sm flex-shrink-0">
                    <span class="avatar-title bg-secondary-subtle text-secondary rounded fs-3">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </span>
                </div>
                <div class="flex-grow-1 ms-3">
                    <p class="text-uppercase fw-medium fs-12 text-muted mb-1">
                        {{ __('translation.reports.teachers_balance') }}
                    </p>
                    <h5 class="mb-0 text-secondary">{{ money($cardsData['teachersBalance']) }}</h5>
                </div>
            </div>
        </div>
    </div>

    <!-- Company Account -->
    <div class="col-lg-4 col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center">
                <div class="avatar-sm flex-shrink-0">
                    <span class="avatar-title bg-dark-subtle text-dark rounded fs-3">
                        <i class="fas fa-building"></i>
                    </span>
                </div>
                <div class="flex-grow-1 ms-3">
                    <p class="text-uppercase fw-medium fs-12 text-muted mb-1">
                        {{ __('translation.reports.main_account_balance') }}
                    </p>
                    <h5 class="mb-0 text-dark">{{ money($cardsData['mainAccountBalance']) }}</h5>
                </div>
            </div>
        </div>
    </div>

    <!-- Groups Account -->
    <div class="col-lg-4 col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title bg-success-subtle text-success rounded fs-3">
                            <i class="fas fa-users"></i>
                        </span>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <p class="text-uppercase fw-medium fs-12 text-muted mb-1">
                            {{ __('translation.reports.groups_balance') }}
                        </p>
                        <h5 class="mb-0 text-success">{{ money($cardsData['groupsBalance']) }}</h5>
                    </div>
                </div>
                <div class="flex-shrink-0">
                    <form method="POST" action="{{ route('admin.reports.process-group-payments') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-success rounded-circle"
                            data-bs-toggle="tooltip" data-bs-placement="top" title="@lang('translation.process_group_payments')"
                            style="width: 32px; height: 32px;">
                            <i class="ri-exchange-line"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>



<script>
    // Initialize tooltips when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Bootstrap tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
