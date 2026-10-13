<div>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>@lang('translation.users.user')</th>
                    <th class="text-center">@lang('translation.leads.responsible_leads')</th>
                    <th class="text-center">@lang('translation.leads.contacted_leads')</th>
                    <th class="text-center">@lang('translation.leads.total_calls')</th>
                    <th class="text-center">@lang('translation.leads.answered')</th>
                    <th class="text-center">@lang('translation.leads.not_answered')</th>
                    <th class="text-center">@lang('translation.leads.converted_to_students')</th>
                    <th class="text-center">@lang('translation.leads.conversion_rate')</th>
                    <th class="text-center">@lang('translation.leads.total_time')</th>
                    <th class="text-center">@lang('translation.leads.average_time')</th>
                </tr>
            </thead>
            <tbody>
                @if(isset($operatorRows) && $operatorRows->count() > 0)
                    @foreach($operatorRows as $row)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm me-2">
                                        <div class="avatar-title bg-primary-subtle text-primary rounded-circle">
                                            {{ $row->user_name ? substr($row->user_name, 0, 1) : 'U' }}
                                        </div>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">{{ $row->user_name ?? __('translation.users.unknown_user') }}</h6>
                                        @if(!empty($row->user_email))
                                            <small class="text-muted">{{ $row->user_email }}</small>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                <strong>{{ number_format($row->responsible_leads) }}</strong>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-info-subtle text-info">
                                    {{ number_format($row->contacted_leads) }}
                                </span>
                            </td>
                            <td class="text-center">{{ number_format($row->total_calls) }}</td>
                            <td class="text-center">
                                <span class="badge bg-success-subtle text-success">
                                    {{ number_format($row->answered_calls) }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-danger-subtle text-danger">
                                    {{ number_format($row->not_answered_calls) }}
                                </span>
                            </td>
                            <td class="text-center">
                                <strong>{{ number_format($row->student_leads) }}</strong>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-primary-subtle text-primary">
                                    {{ $row->conversion_rate }}%
                                </span>
                            </td>
                            <td class="text-center">{{ $row->formatted_total_duration }}</td>
                            <td class="text-center text-muted">{{ $row->formatted_average_duration }}</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="10" class="text-center text-muted py-4">
                            <i class="ri-user-search-line fs-3 d-block mb-2"></i>
                            @lang('translation.leads.no_operator_leads_found')
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>
