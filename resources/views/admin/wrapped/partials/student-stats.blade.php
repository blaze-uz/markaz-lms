@if (isset($wrapped['message']))
    <div class="alert alert-info text-center">
        <p>{{ $wrapped['message'] }}</p>
    </div>
@else
    <div class="row">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="stat-card text-center">
                <div class="stat-number">{{ number_format($wrapped['stats']['total_groups']) }}</div>
                <div class="stat-label">Groups Joined</div>
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
                <div class="stat-number">{{ number_format($wrapped['stats']['total_attendance']) }}</div>
                <div class="stat-label">Total Attendance</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="stat-card text-center">
                <div class="stat-number">{{ number_format($wrapped['stats']['present_count']) }}</div>
                <div class="stat-label">Present Days</div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 col-sm-6 mb-3">
            <div class="stat-card text-center">
                <div class="stat-number">{{ $wrapped['stats']['attendance_percentage'] }}%</div>
                <div class="stat-label">Attendance Rate</div>
            </div>
        </div>
        <div class="col-md-4 col-sm-6 mb-3">
            <div class="stat-card text-center">
                <div class="stat-number">{{ number_format($wrapped['stats']['total_payments'], 0) }}</div>
                <div class="stat-label">Total Payments</div>
            </div>
        </div>
        <div class="col-md-4 col-sm-6 mb-3">
            <div class="stat-card text-center">
                @if ($wrapped['stats']['average_score'])
                    <div class="stat-number">{{ $wrapped['stats']['average_score'] }}</div>
                    <div class="stat-label">Average Score</div>
                @else
                    <div class="stat-label">No scores yet</div>
                @endif
            </div>
        </div>
    </div>

    @if (isset($wrapped['stats']['top_course']) && $wrapped['stats']['top_course'] !== 'N/A')
        <div class="text-center mb-3">
            <div class="top-course-badge">
                <i class="fas fa-trophy me-2"></i>
                Top Course: {{ $wrapped['stats']['top_course'] }}
            </div>
        </div>
    @endif

    @if (!empty($wrapped['charts']['attendance_by_month']))
        <div class="chart-container">
            <h5 class="mb-3">Your Attendance by Month</h5>
            <div id="chart-attendance_by_month"></div>
        </div>
    @endif

    @if (!empty($wrapped['charts']['payments_by_month']))
        <div class="chart-container">
            <h5 class="mb-3">Your Payments by Month</h5>
            <div id="chart-payments_by_month"></div>
        </div>
    @endif
@endif

