<div class="row">
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="stat-card text-center">
            <div class="stat-number">{{ number_format($wrapped['stats']['total_groups']) }}</div>
            <div class="stat-label">Total Groups</div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="stat-card text-center">
            <div class="stat-number">{{ number_format($wrapped['stats']['active_groups']) }}</div>
            <div class="stat-label">Active Groups</div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="stat-card text-center">
            <div class="stat-number">{{ number_format($wrapped['stats']['completed_groups']) }}</div>
            <div class="stat-label">Completed Groups</div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="stat-card text-center">
            <div class="stat-number">{{ number_format($wrapped['stats']['total_students']) }}</div>
            <div class="stat-label">Students Taught</div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4 col-sm-6 mb-3">
        <div class="stat-card text-center">
            <div class="stat-number">{{ number_format($wrapped['stats']['total_attendance_marked']) }}</div>
            <div class="stat-label">Attendance Marked</div>
        </div>
    </div>
    <div class="col-md-4 col-sm-6 mb-3">
        <div class="stat-card text-center">
            <div class="stat-number">{{ number_format($wrapped['stats']['present_count']) }}</div>
            <div class="stat-label">Present Records</div>
        </div>
    </div>
    <div class="col-md-4 col-sm-6 mb-3">
        <div class="stat-card text-center">
            <div class="top-course-badge">
                <i class="fas fa-trophy me-2"></i>
                Top Course: {{ $wrapped['stats']['top_course'] }}
            </div>
        </div>
    </div>
</div>

@if (!empty($wrapped['charts']['attendance_by_month']))
    <div class="chart-container">
        <h5 class="mb-3">Attendance Marked by Month</h5>
        <div id="chart-attendance_by_month"></div>
    </div>
@endif

@if (!empty($wrapped['charts']['groups_by_month']))
    <div class="chart-container">
        <h5 class="mb-3">Groups Created by Month</h5>
        <div id="chart-groups_by_month"></div>
    </div>
@endif

