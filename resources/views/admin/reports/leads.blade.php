@extends('layouts.master')
@section('title')
    @lang('translation.reports.leads')
@endsection
@section('css')
    <link href="{{ asset('assets/libs/choices.js/choices.js.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/libs/flatpickr/flatpickr.min.css') }}" rel="stylesheet" type="text/css">
    <style>
        .report-card {
            transition: all 0.3s ease;
        }

        .report-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .stats-card {
            border-left: 4px solid;
        }

        .stats-card.primary {
            border-left-color: #3f6ad8;
        }

        .stats-card.success {
            border-left-color: #47ad4b;
        }

        .stats-card.warning {
            border-left-color: #ffab00;
        }

        .stats-card.info {
            border-left-color: #2196f3;
        }

        .stats-card.danger {
            border-left-color: #ff5722;
        }

        .funnel-chart .funnel-step {
            position: relative;
            margin-bottom: 15px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border: 1px solid #dee2e6;
            transition: all 0.3s ease;
        }

        .funnel-chart .funnel-step:hover {
            transform: translateX(5px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .funnel-chart .funnel-bar {
            position: relative;
            margin: 0 20px;
            transition: width 0.8s ease-in-out;
        }

        .funnel-chart .funnel-label h6 {
            font-weight: 600;
            color: #495057;
        }

        .funnel-chart .funnel-percentage {
            min-width: 60px;
            font-weight: bold;
        }

        .performance-metric {
            background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%);
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 1rem;
            transition: all 0.3s ease;
        }

        .performance-metric:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
    </style>
@endsection
@section('content')
    {{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}
    <!-- Work in Progress Banner -->
    {{-- <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-warning" role="alert">
                <div class="d-flex align-items-center">
                    <i class="ri-tools-fill fs-3 me-2"></i>
                    <div>
                        <h5 class="alert-heading mb-1">@lang('translation.work_in_progress')</h5>
                        <p class="mb-0">@lang('translation.page_under_construction')</p>
                    </div>
                </div>
            </div>
        </div>
    </div> --}}
    <!-- Filter Form -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body py-3">
                    <form action="{{ route('admin.reports.leads') }}" class="row align-items-end" method="GET">
                        <div class="col-md-3">
                            <label class="form-label">@lang('attr.from')</label>
                            <input name="from" type="text" class="form-control" data-provider="flatpickr"
                                data-date-format="Y-m-d" placeholder="{{ __('attr.from') }}"
                                value="{{ old('from', $startDate) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">@lang('attr.to')</label>
                            <input name="to" type="text" class="form-control" data-provider="flatpickr"
                                data-date-format="Y-m-d" placeholder="{{ __('attr.to') }}"
                                value="{{ old('to', $endDate) }}">
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="ri-filter-line me-1"></i>
                                @lang('translation.Filter')
                            </button>
                            <a href="{{ route('admin.reports.leads') }}" class="btn btn-outline-secondary ms-2">
                                <i class="ri-refresh-line me-1"></i>
                                @lang('translation.reset')
                            </a>
                        </div>
                        <div class="col-md-3 text-end">
                            <button type="button" class="btn btn-success" onclick="exportReport()">
                                <i class="ri-download-line me-1"></i>
                                @lang('translation.export')
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Leads Overview Cards -->
    <livewire:report.leads.cards :start-date="request()->from" :end-date="request()->to" lazy />


    <!-- Leads Analytics Row -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <!-- Conversion Funnel -->
            <div class="card report-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ri-funnel-line me-2"></i>
                        @lang('translation.leads.conversion_funnel')
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Conversion Funnel Visualization -->
                    <div class="funnel-chart">
                        <div
                            class="funnel-step d-flex justify-content-between align-items-center mb-3 p-3 bg-light rounded">
                            <div class="funnel-label">
                                <h6 class="mb-1">@lang('translation.leads.total_leads')</h6>
                                <span
                                    class="text-muted">{{ number_format($leadStats['conversion_funnel']['total']) }}</span>
                            </div>
                            <div class="funnel-bar bg-secondary" style="width: 100%; height: 8px; border-radius: 4px;">
                            </div>
                            <div class="funnel-percentage text-end">
                                <strong>100%</strong>
                            </div>
                        </div>

                        <div
                            class="funnel-step d-flex justify-content-between align-items-center mb-3 p-3 bg-light rounded">
                            <div class="funnel-label">
                                <h6 class="mb-1">@lang('translation.leads.contacted')</h6>
                                <span
                                    class="text-muted">{{ number_format($leadStats['conversion_funnel']['contacted']) }}</span>
                            </div>
                            <div class="funnel-bar bg-primary"
                                style="width: {{ $leadStats['conversion_funnel']['percentages']['contacted'] }}%; height: 8px; border-radius: 4px;">
                            </div>
                            <div class="funnel-percentage text-end">
                                <strong>{{ $leadStats['conversion_funnel']['percentages']['contacted'] }}%</strong>
                            </div>
                        </div>

                        <div
                            class="funnel-step d-flex justify-content-between align-items-center mb-3 p-3 bg-light rounded">
                            <div class="funnel-label">
                                <h6 class="mb-1">@lang('translation.leads.qualified')</h6>
                                <span
                                    class="text-muted">{{ number_format($leadStats['conversion_funnel']['qualified']) }}</span>
                            </div>
                            <div class="funnel-bar bg-warning"
                                style="width: {{ $leadStats['conversion_funnel']['percentages']['qualified'] }}%; height: 8px; border-radius: 4px;">
                            </div>
                            <div class="funnel-percentage text-end">
                                <strong>{{ $leadStats['conversion_funnel']['percentages']['qualified'] }}%</strong>
                            </div>
                        </div>

                        <div class="funnel-step d-flex justify-content-between align-items-center p-3 bg-light rounded">
                            <div class="funnel-label">
                                <h6 class="mb-1">@lang('translation.leads.converted')</h6>
                                <span
                                    class="text-muted">{{ number_format($leadStats['conversion_funnel']['converted']) }}</span>
                            </div>
                            <div class="funnel-bar bg-success"
                                style="width: {{ $leadStats['conversion_funnel']['percentages']['converted'] }}%; height: 8px; border-radius: 4px;">
                            </div>
                            <div class="funnel-percentage text-end">
                                <strong>{{ $leadStats['conversion_funnel']['percentages']['converted'] }}%</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Fallback to Livewire component if needed -->
                    <div class="mt-3">
                        <livewire:report.leads.funnel :start-date="$startDate" :end-date="$endDate" />
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <!-- Lead Sources -->
            <div class="card report-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ri-pie-chart-line me-2"></i>
                        @lang('translation.leads.sources')
                    </h5>
                </div>
                <div class="card-body">
                    <livewire:report.leads.incoming-statistics :start-date="request()->from" :end-date="request()->to" />
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Metrics Row -->
    <div class="row mb-4">
        <div class="col-lg-6">
            <!-- Calls Performance -->
            <div class="card report-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ri-phone-line me-2"></i>
                        @lang('translation.leads.calls_performance')
                    </h5>
                </div>
                <div class="card-body">
                    <livewire:report.leads.calls-statistics :start-date="request()->from" :end-date="request()->to" />
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <!-- Lead Statistics -->
            <div class="card report-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ri-bar-chart-line me-2"></i>
                        @lang('translation.leads.statistics')
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="p-3 border rounded stats-card primary">
                                <h4 class="text-primary mb-1">
                                    {{ number_format($leadStats['overview']['todays_leads']) }}
                                </h4>
                                <p class="text-muted mb-0 small">@lang('translation.leads.todays_leads')</p>
                                @if ($leadStats['performance_summary']['trends']['total_leads_trend'] > 0)
                                    <small class="text-success">
                                        <i class="ri-arrow-up-line"></i>
                                        +{{ $leadStats['performance_summary']['trends']['total_leads_trend'] }}%
                                    </small>
                                @else
                                    <small class="text-danger">
                                        <i class="ri-arrow-down-line"></i>
                                        {{ $leadStats['performance_summary']['trends']['total_leads_trend'] }}%
                                    </small>
                                @endif
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="p-3 border rounded stats-card success">
                                <h4 class="text-success mb-1">
                                    {{ number_format($leadStats['overview']['todays_conversions']) }}
                                </h4>
                                <p class="text-muted mb-0 small">@lang('translation.leads.todays_conversions')</p>
                                @if ($leadStats['performance_summary']['trends']['converted_leads_trend'] > 0)
                                    <small class="text-success">
                                        <i class="ri-arrow-up-line"></i>
                                        +{{ $leadStats['performance_summary']['trends']['converted_leads_trend'] }}%
                                    </small>
                                @else
                                    <small class="text-danger">
                                        <i class="ri-arrow-down-line"></i>
                                        {{ $leadStats['performance_summary']['trends']['converted_leads_trend'] }}%
                                    </small>
                                @endif
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 border rounded stats-card warning">
                                <h4 class="text-warning mb-1">
                                    {{ number_format($leadStats['overview']['week_leads']) }}
                                </h4>
                                <p class="text-muted mb-0 small">@lang('translation.leads.this_week')</p>
                                <small class="text-info">
                                    <i class="ri-calendar-line"></i>
                                    {{ $leadStats['overview']['conversion_rate'] }}% @lang('translation.leads.converted')
                                </small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 border rounded stats-card info">
                                <h4 class="text-info mb-1">
                                    {{ number_format($leadStats['overview']['month_leads']) }}
                                </h4>
                                <p class="text-muted mb-0 small">@lang('translation.leads.this_month')</p>
                                <small class="text-success">
                                    <i class="ri-trophy-line"></i>
                                    {{ $leadStats['time_insights']['quality_score'] }}/100 @lang('translation.leads.quality')
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Lead Source Analysis Row -->
    <div class="row mb-4">
        <div class="col-lg-12">
            <!-- Lead Source Breakdown -->
            <div class="card report-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ri-pie-chart-2-line me-2"></i>
                        @lang('translation.leads.source_analysis')
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-borderless align-middle">
                            <thead>
                                <tr>
                                    <th>@lang('translation.leads.source')</th>
                                    <th>@lang('translation.leads.total_leads')</th>
                                    <th>@lang('translation.leads.conversions')</th>
                                    <th>@lang('translation.leads.conversion_rate')</th>
                                    <th>@lang('translation.status')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($leadStats['source_analysis'] as $source)
                                    <tr>
                                        <td>
                                            <span class="badge bg-primary-subtle text-primary">
                                                {{ $source->collection ? $source->collection->name : __('translation.leads.direct_lead') }}
                                            </span>
                                        </td>
                                        <td>
                                            <strong>{{ number_format($source->total_count) }}</strong>
                                            <div class="small text-muted">Performance:
                                                {{ $source->performance_score }}/100</div>
                                        </td>
                                        <td><span
                                                class="text-success">{{ number_format($source->converted_count) }}</span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <span
                                                    class="badge bg-{{ $source->conversion_rate > 50 ? 'success' : ($source->conversion_rate > 25 ? 'warning' : 'danger') }}-subtle text-{{ $source->conversion_rate > 50 ? 'success' : ($source->conversion_rate > 25 ? 'warning' : 'danger') }}">
                                                    {{ $source->conversion_rate }}%
                                                </span>
                                                <div class="progress ms-2" style="width: 60px; height: 6px;">
                                                    <div class="progress-bar bg-{{ $source->conversion_rate > 50 ? 'success' : ($source->conversion_rate > 25 ? 'warning' : 'danger') }}"
                                                        style="width: {{ min($source->conversion_rate, 100) }}%"></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <i
                                                class="ri-arrow-{{ $source->conversion_rate > 30 ? 'up' : 'down' }}-line text-{{ $source->conversion_rate > 30 ? 'success' : 'danger' }}"></i>
                                            <small
                                                class="d-block text-muted">{{ $source->performance_score > 70 ? 'Excellent' : ($source->performance_score > 50 ? 'Good' : 'Needs Improvement') }}</small>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            <i class="ri-inbox-line fs-3 d-block mb-2"></i>
                                            @lang('translation.no_data')
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if (!empty($leadStats['select_custom_fields']))
        <div class="row mb-4">
            <div class="col-lg-12">
                <div class="card report-card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="ri-list-check me-2"></i>
                            @lang('translation.leads.custom_select_field_stats')
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach ($leadStats['select_custom_fields'] as $fieldStats)
                                <div class="col-lg-6 mb-4">
                                    <h6 class="border-bottom pb-2 mb-3">{{ $fieldStats['field_label'] }}</h6>
                                    <p class="text-muted small mb-3">
                                        @lang('translation.leads.total_leads'):
                                        {{ number_format($fieldStats['total']) }}
                                    </p>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-borderless align-middle mb-0">
                                            <tbody>
                                                @foreach ($fieldStats['breakdown'] as $row)
                                                    <tr>
                                                        <td style="width: 38%">
                                                            <span
                                                                class="badge bg-primary-subtle text-primary">{{ $row['label'] }}</span>
                                                        </td>
                                                        <td class="text-nowrap">
                                                            <strong>{{ number_format($row['count']) }}</strong>
                                                        </td>
                                                        <td>
                                                            <div class="d-flex align-items-center gap-2">
                                                                <div class="progress flex-grow-1" style="height: 8px">
                                                                    <div class="progress-bar bg-primary"
                                                                        style="width: {{ min($row['percent'], 100) }}%">
                                                                    </div>
                                                                </div>
                                                                <span
                                                                    class="small text-muted text-nowrap">{{ $row['percent'] }}%</span>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                                @if ($fieldStats['empty_count'] > 0)
                                                    <tr>
                                                        <td>
                                                            <span
                                                                class="badge bg-secondary-subtle text-secondary">@lang('translation.leads.not_set_or_other')</span>
                                                        </td>
                                                        <td class="text-nowrap">
                                                            <strong>{{ number_format($fieldStats['empty_count']) }}</strong>
                                                        </td>
                                                        <td>
                                                            <div class="d-flex align-items-center gap-2">
                                                                <div class="progress flex-grow-1" style="height: 8px">
                                                                    <div class="progress-bar bg-secondary"
                                                                        style="width: {{ min($fieldStats['empty_percent'], 100) }}%">
                                                                    </div>
                                                                </div>
                                                                <span
                                                                    class="small text-muted text-nowrap">{{ $fieldStats['empty_percent'] }}%</span>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Operator Lead Responsibility Row -->
    <div class="row mb-4">
        <div class="col-lg-12">
            <div class="card report-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ri-user-star-line me-2"></i>
                        @lang('translation.leads.operator_responsibility_report')
                    </h5>
                </div>
                <div class="card-body">
                    <livewire:report.leads.operator-responsibility-table :start-date="request()->from" :end-date="request()->to" lazy />
                </div>
            </div>
        </div>
    </div>

    <!-- User Calls Report Row -->
    <div class="row mb-4">
        <div class="col-lg-12">
            <!-- User Calls Table -->
            <div class="card report-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ri-user-phone-line me-2"></i>
                        @lang('translation.leads.user_calls_report')
                    </h5>
                </div>
                <div class="card-body">
                    <livewire:report.leads.user-calls-table :start-date="request()->from" :end-date="request()->to" lazy />
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Leads Activity Row -->
    <div class="row mb-4">
        <div class="col-lg-12">
            <!-- Recent Leads -->
            <div class="card report-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="ri-time-line me-2"></i>
                        @lang('translation.leads.recent_activity')
                    </h5>
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-primary active"
                            onclick="filterRecentLeads('today')">@lang('translation.today')</button>
                        <button type="button" class="btn btn-outline-primary"
                            onclick="filterRecentLeads('week')">@lang('translation.leads.this_week')</button>
                        <button type="button" class="btn btn-outline-primary"
                            onclick="filterRecentLeads('month')">@lang('translation.leads.this_month')</button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>@lang('translation.leads.lead_name')</th>
                                    <th>@lang('translation.leads.source')</th>
                                    <th>@lang('translation.status')</th>
                                    <th>@lang('translation.leads.created_at')</th>
                                    <th>@lang('translation.leads.last_contact')</th>
                                    <th>@lang('translation.actions')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($leadStats['recent_activity'] as $lead)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm me-2">
                                                    <div
                                                        class="avatar-title bg-primary-subtle text-primary rounded-circle">
                                                        {{ substr($lead->name ?? 'N', 0, 1) }}
                                                    </div>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0">
                                                        {{ $lead->name ?? __('translation.leads.no_name') }}</h6>
                                                    <small
                                                        class="text-muted">{{ $lead->phone ?? ($lead->email ?? __('translation.leads.no_contact_info')) }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-info-subtle text-info">
                                                {{ $lead->collection ? $lead->collection->name : __('translation.leads.direct_lead') }}
                                            </span>
                                        </td>
                                        <td>
                                            <span
                                                class="badge bg-{{ $lead->status === 'student'
                                                    ? 'success'
                                                    : ($lead->status === 'qualified'
                                                        ? 'primary'
                                                        : ($lead->status === 'contacted'
                                                            ? 'warning'
                                                            : 'secondary')) }}-subtle text-{{ $lead->status === 'student'
                                                    ? 'success'
                                                    : ($lead->status === 'qualified'
                                                        ? 'primary'
                                                        : ($lead->status === 'contacted'
                                                            ? 'warning'
                                                            : 'secondary')) }}">
                                                {{ $lead->status === 'student' ? __('translation.leads.converted') : __(ucfirst($lead->status ?? 'new')) }}
                                            </span>
                                        </td>
                                        <td>
                                            <small>{{ $lead->created_at->format('M d, Y') }}</small><br>
                                            <small class="text-muted">{{ $lead->created_at->format('H:i') }}</small>
                                        </td>
                                        <td>
                                            @if ($lead->calls->count() > 0)
                                                <small>{{ $lead->calls->first()->created_at->diffForHumans() }}</small>
                                            @else
                                                <small class="text-muted">@lang('translation.leads.no_contact_yet')</small>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('admin.leads.details', $lead->id) }}"
                                                    class="btn btn-outline-primary btn-sm">
                                                    <i class="ri-eye-line"></i>
                                                </a>
                                                <a href="{{ route('admin.leads.edit', $lead->id) }}"
                                                    class="btn btn-outline-secondary btn-sm">
                                                    <i class="ri-edit-line"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            <i class="ri-user-search-line fs-3 d-block mb-2"></i>
                                            @lang('translation.leads.no_recent_leads')
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Summary Row -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <!-- Performance Summary -->
            <div class="card report-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ri-dashboard-line me-2"></i>
                        @lang('translation.leads.performance_summary')
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <div class="text-center p-3 border rounded stats-card primary">
                                <h3 class="text-primary mb-1">{{ number_format($leadStats['overview']['total_leads']) }}
                                </h3>
                                <p class="text-muted mb-0">@lang('translation.leads.total_leads')</p>
                                @if ($leadStats['performance_summary']['trends']['total_leads_trend'] > 0)
                                    <small class="text-success">
                                        <i class="ri-arrow-up-line"></i>
                                        +{{ $leadStats['performance_summary']['trends']['total_leads_trend'] }}%
                                        @lang('translation.leads.vs_last_period')
                                    </small>
                                @else
                                    <small class="text-danger">
                                        <i class="ri-arrow-down-line"></i>
                                        {{ $leadStats['performance_summary']['trends']['total_leads_trend'] }}%
                                        @lang('translation.leads.vs_last_period')
                                    </small>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="text-center p-3 border rounded stats-card success">
                                <h3 class="text-success mb-1">
                                    {{ number_format($leadStats['overview']['converted_leads']) }}</h3>
                                <p class="text-muted mb-0">@lang('translation.leads.converted')</p>
                                @if ($leadStats['performance_summary']['trends']['converted_leads_trend'] > 0)
                                    <small class="text-success">
                                        <i class="ri-arrow-up-line"></i>
                                        +{{ $leadStats['performance_summary']['trends']['converted_leads_trend'] }}%
                                        @lang('translation.leads.vs_last_period')
                                    </small>
                                @else
                                    <small class="text-danger">
                                        <i class="ri-arrow-down-line"></i>
                                        {{ $leadStats['performance_summary']['trends']['converted_leads_trend'] }}%
                                        @lang('translation.leads.vs_last_period')
                                    </small>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="text-center p-3 border rounded stats-card info">
                                <h3 class="text-info mb-1">{{ number_format($leadStats['overview']['qualified_leads']) }}
                                </h3>
                                <p class="text-muted mb-0">@lang('translation.leads.qualified')</p>
                                @if ($leadStats['performance_summary']['trends']['qualified_leads_trend'] > 0)
                                    <small class="text-success">
                                        <i class="ri-arrow-up-line"></i>
                                        +{{ $leadStats['performance_summary']['trends']['qualified_leads_trend'] }}%
                                        @lang('translation.leads.vs_last_period')
                                    </small>
                                @else
                                    <small class="text-danger">
                                        <i class="ri-arrow-down-line"></i>
                                        {{ $leadStats['performance_summary']['trends']['qualified_leads_trend'] }}%
                                        @lang('translation.leads.vs_last_period')
                                    </small>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="text-center p-3 border rounded stats-card warning">
                                <h3 class="text-warning mb-1">{{ $leadStats['overview']['conversion_rate'] }}%</h3>
                                <p class="text-muted mb-0">@lang('translation.leads.conversion_rate')</p>
                                @if ($leadStats['performance_summary']['trends']['conversion_rate_trend'] > 0)
                                    <small class="text-success">
                                        <i class="ri-arrow-up-line"></i>
                                        +{{ $leadStats['performance_summary']['trends']['conversion_rate_trend'] }}%
                                        @lang('translation.leads.vs_last_period')
                                    </small>
                                @else
                                    <small class="text-danger">
                                        <i class="ri-arrow-down-line"></i>
                                        {{ $leadStats['performance_summary']['trends']['conversion_rate_trend'] }}%
                                        @lang('translation.leads.vs_last_period')
                                    </small>
                                @endif
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="mb-3">@lang('translation.leads.status_distribution')</h6>
                            <div class="mb-2">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="small">@lang('translation.leads.converted')</span>
                                    <span class="small fw-bold">{{ $leadStats['overview']['conversion_rate'] }}%</span>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-success"
                                        style="width: {{ $leadStats['overview']['conversion_rate'] }}%">
                                    </div>
                                </div>
                            </div>
                            <div class="mb-2">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="small">@lang('translation.leads.qualified')</span>
                                    <span class="small fw-bold">{{ $leadStats['overview']['qualification_rate'] }}%</span>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-primary"
                                        style="width: {{ $leadStats['overview']['qualification_rate'] }}%">
                                    </div>
                                </div>
                            </div>
                            <div class="mb-2">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="small">@lang('translation.leads.contacted')</span>
                                    @php
                                        $contactedRate =
                                            $leadStats['overview']['total_leads'] > 0
                                                ? round(
                                                    ($leadStats['overview']['contacted_leads'] /
                                                        $leadStats['overview']['total_leads']) *
                                                        100,
                                                    1,
                                                )
                                                : 0;
                                    @endphp
                                    <span class="small fw-bold">{{ $contactedRate }}%</span>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-warning" style="width: {{ $contactedRate }}%">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="mb-3">@lang('translation.leads.quick_insights')</h6>
                            <ul class="list-unstyled">
                                <li class="mb-2">
                                    <i class="ri-check-line text-success me-2"></i>
                                    @lang('translation.leads.best_performing_day'): {{ $leadStats['time_insights']['best_day'] }}
                                </li>
                                <li class="mb-2">
                                    <i class="ri-time-line text-info me-2"></i>
                                    @lang('translation.leads.peak_hours'): {{ $leadStats['time_insights']['peak_hour'] }}:00 -
                                    {{ $leadStats['time_insights']['peak_hour'] + 2 }}:00
                                </li>
                                <li class="mb-2">
                                    <i class="ri-phone-line text-warning me-2"></i>
                                    @lang('translation.leads.avg_response_time'): {{ $leadStats['time_insights']['avg_response_time'] }}
                                    @lang('translation.leads.hours')
                                </li>
                                <li class="mb-2">
                                    <i class="ri-star-line text-primary me-2"></i>
                                    @lang('translation.leads.quality_score'): {{ $leadStats['time_insights']['quality_score'] }}/100
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="card report-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ri-tools-line me-2"></i>
                        @lang('translation.leads.quick_actions')
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-primary" onclick="exportLeadsData()">
                            <i class="ri-download-2-line me-1"></i>
                            @lang('translation.leads.export_all_leads')
                        </button>
                        <button type="button" class="btn btn-success" onclick="scheduleReport()">
                            <i class="ri-calendar-schedule-line me-1"></i>
                            @lang('translation.leads.schedule_report')
                        </button>
                        <button type="button" class="btn btn-info" onclick="generateAnalysis()">
                            <i class="ri-ai-generate me-1"></i>
                            @lang('translation.leads.ai_analysis')
                        </button>
                        <hr>
                        <a href="{{ route('admin.leads.index') }}" class="btn btn-outline-primary">
                            <i class="ri-user-search-line me-1"></i>
                            @lang('translation.leads.manage_leads')
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="{{ asset('assets/js/apex/apex.min.js') }}"></script>
    <script>
        // Export functionality
        function exportReport() {
            // Generate a simple report export
            const from = document.querySelector('input[name="from"]').value;
            const to = document.querySelector('input[name="to"]').value;

            let params = new URLSearchParams();
            if (from) params.append('from', from);
            if (to) params.append('to', to);
            params.append('export', 'true');

            const url = '{{ route('admin.reports.leads') }}?' + params.toString();
            window.open(url, '_blank');
        }

        function exportLeadsData() {
            // Redirect to leads page with export parameters
            const from = document.querySelector('input[name="from"]').value;
            const to = document.querySelector('input[name="to"]').value;

            let params = new URLSearchParams();
            if (from) params.append('from', from);
            if (to) params.append('to', to);

            const url = '{{ route('admin.leads.index') }}?' + params.toString();
            window.open(url, '_blank');
        }

        function scheduleReport() {
            Swal.fire({
                title: @json(__('translation.leads.schedule_report')),
                html: `
            <div class="text-start">
                <div class="mb-3">
                    <label class="form-label">{{ __('translation.leads.frequency') }}</label>
                    <select class="form-select" id="frequency">
                        <option value="daily">{{ __('translation.leads.daily') }}</option>
                        <option value="weekly">{{ __('translation.leads.weekly') }}</option>
                        <option value="monthly">{{ __('translation.leads.monthly') }}</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ __('translation.Email') }}</label>
                    <input type="email" class="form-control" id="email" placeholder="{{ __('translation.leads.enter_email') }}">
                </div>
            </div>
        `,
                showCancelButton: true,
                confirmButtonText: @json(__('translation.leads.schedule')),
                cancelButtonText: @json(__('translation.Cancel')),
                preConfirm: () => {
                    const frequency = document.getElementById('frequency').value;
                    const email = document.getElementById('email').value;

                    if (!email) {
                        Swal.showValidationMessage(@json(__('translation.leads.email_required')));
                        return false;
                    }

                    return {
                        frequency,
                        email
                    };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire(
                        @json(__('translation.leads.scheduled')),
                        @json(__('translation.leads.report_scheduled_success')),
                        'success'
                    );
                }
            });
        }

        function generateAnalysis() {
            Swal.fire({
                title: @json(__('translation.leads.generating_analysis')),
                text: @json(__('translation.leads.please_wait_analysis')),
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            setTimeout(() => {
                Swal.fire({
                    title: @json(__('translation.leads.analysis_complete')),
                    html: `
                <div class="text-start">
                    <h6>{{ __('translation.leads.key_insights') }}:</h6>
                    <ul class="list-unstyled">
                        <li><i class="ri-check-line text-success me-2"></i>{{ __('translation.leads.insight_conversion_increase') }}</li>
                        <li><i class="ri-check-line text-success me-2"></i>{{ __('translation.leads.insight_best_collection') }}</li>
                        <li><i class="ri-alert-line text-warning me-2"></i>{{ __('translation.leads.insight_calls_improvement') }}</li>
                        <li><i class="ri-lightbulb-line text-info me-2"></i>{{ __('translation.leads.insight_weekend_focus') }}</li>
                    </ul>
                </div>
            `,
                    icon: 'info',
                    confirmButtonText: @json(__('translation.leads.view_detailed_report'))
                });
            }, 3000);
        }

        // Recent leads filter functionality
        function filterRecentLeads(period) {
            // Remove active class from all buttons
            document.querySelectorAll('.btn-group .btn').forEach(btn => {
                btn.classList.remove('active');
            });

            // Add active class to clicked button
            event.target.classList.add('active');

            // Here you could implement actual filtering logic
            console.log('Filtering recent leads for period:', period);
        }

        // Auto-refresh functionality
        let autoRefreshInterval;

        function toggleAutoRefresh() {
            if (autoRefreshInterval) {
                clearInterval(autoRefreshInterval);
                autoRefreshInterval = null;
            } else {
                autoRefreshInterval = setInterval(() => {
                    // Refresh Livewire components
                    if (window.Livewire) {
                        window.Livewire.emit('refreshData');
                    }
                }, 30000); // Refresh every 30 seconds
            }
        }

        // Add keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey || e.metaKey) {
                switch (e.key) {
                    case 'e':
                        e.preventDefault();
                        exportReport();
                        break;
                    case 'r':
                        e.preventDefault();
                        location.reload();
                        break;
                }
            }
        });
    </script>
@endsection

@section('script-top')
    <script src="{{ asset('assets/libs/flatpickr/flatpickr.min.js') }}"></script>
@endsection
