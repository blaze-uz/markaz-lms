<div>
    <div class="card rounded-4 card-height-100">
        <div class="card-header rounded-top-4">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">@lang('translation.dashboards.daily_spending')</h5>
                <div class="text-muted">
                    <small>{{ $startDate }} - {{ $endDate }}</small>
                </div>
            </div>
        </div>
        <div class="card-body">
            @if ($dailySpending->count() > 0)
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="text-center">
                            <h4 class="text-primary">{{ money($totalSpent) }}</h4>
                            <small class="text-muted">@lang('translation.dashboards.total_spent')</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center">
                            <h4 class="text-success">{{ $totalSms }}</h4>
                            <small class="text-muted">@lang('translation.dashboards.total_sms_sent')</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center">
                            <h4 class="text-info">{{ money($averagePerDay) }}</h4>
                            <small class="text-muted">@lang('translation.dashboards.average_per_day')</small>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>@lang('translation.dashboards.date')</th>
                                <th class="text-end">@lang('translation.dashboards.sms_count')</th>
                                <th class="text-end">@lang('translation.dashboards.amount_spent')</th>
                                <th class="text-end">@lang('translation.dashboards.average_per_sms')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($dailySpending as $day)
                                <tr>
                                    <td>
                                        <strong>{{ dateFormat($day->date) }}</strong>
                                        <br>
                                        <small class="text-muted">{{ dateFormat($day->date, 'l') }}</small>
                                    </td>
                                    <td class="text-end">
                                        <span class="badge bg-primary">{{ $day->sms_count }}</span>
                                    </td>
                                    <td class="text-end">
                                        <strong class="text-success">{{ money($day->total_spent) }}</strong>
                                    </td>
                                    <td class="text-end">
                                        <span
                                            class="text-muted">{{ money(round($day->total_spent / $day->sms_count)) }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <div class="text-muted">
                        <i class="fas fa-chart-line fa-3x mb-3"></i>
                        <h5>@lang('translation.dashboards.no_data_available')</h5>
                        <p>@lang('translation.dashboards.no_spending_data_for_period')</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
