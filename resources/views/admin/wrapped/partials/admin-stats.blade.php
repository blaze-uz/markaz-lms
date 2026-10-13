<div class="row">
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="stat-card text-center">
            <div class="stat-number">{{ number_format($wrapped['stats']['total_students']) }}</div>
            <div class="stat-label">New Students</div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="stat-card text-center">
            <div class="stat-number">{{ number_format($wrapped['stats']['total_groups']) }}</div>
            <div class="stat-label">Groups Created</div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="stat-card text-center">
            <div class="stat-number">{{ number_format($wrapped['stats']['total_payments'], 0) }}</div>
            <div class="stat-label">Total Payments</div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="stat-card text-center">
            <div class="stat-number">{{ number_format($wrapped['stats']['total_attendance']) }}</div>
            <div class="stat-label">Attendance Records</div>
        </div>
    </div>
</div>

@if (!empty($wrapped['charts']['payments_by_month']))
    <div class="chart-container">
        <h5 class="mb-3">Payments by Month</h5>
        <div id="chart-payments_by_month"></div>
    </div>
@endif

@if (!empty($wrapped['charts']['students_by_month']))
    <div class="chart-container">
        <h5 class="mb-3">New Students by Month</h5>
        <div id="chart-students_by_month"></div>
    </div>
@endif

