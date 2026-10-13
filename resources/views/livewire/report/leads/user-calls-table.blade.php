<div>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>@lang('translation.users.user')</th>
                    <th class="text-center">@lang('translation.leads.total_calls')</th>
                    <th class="text-center">@lang('translation.leads.answered')</th>
                    <th class="text-center">@lang('translation.leads.not_answered')</th>
                    <th class="text-center">@lang('translation.leads.busy')</th>
                    <th class="text-center">@lang('translation.leads.declined')</th>
                    <th class="text-center">@lang('translation.leads.total_talk_time')</th>
                    <th class="text-center">@lang('translation.leads.average_talk_time')</th>
                    <th class="text-center">@lang('translation.leads.answer_rate')</th>
                </tr>
            </thead>
            <tbody>
                @if(isset($userCallsData) && $userCallsData->count() > 0)
                    @foreach($userCallsData as $row)
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
                                <strong>{{ number_format($row->total_calls ?? 0) }}</strong>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-success-subtle text-success">
                                    {{ number_format($row->answered_calls ?? 0) }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-warning-subtle text-warning">
                                    {{ number_format($row->not_answered_calls ?? 0) }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-info-subtle text-info">
                                    {{ number_format($row->busy_calls ?? 0) }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-danger-subtle text-danger">
                                    {{ number_format($row->declined_calls ?? 0) }}
                                </span>
                            </td>
                            <td class="text-center">
                                <strong>{{ $row->formatted_total_duration ?? '-' }}</strong>
                            </td>
                            <td class="text-center">
                                <span class="text-muted">{{ $row->formatted_average_duration ?? '-' }}</span>
                            </td>
                            <td class="text-center">
                                @php
                                    $rate = isset($row->answer_rate) ? (float)$row->answer_rate : 0;
                                    $class = $rate >= 50 ? 'success' : ($rate >= 25 ? 'warning' : 'danger');
                                @endphp
                                <div class="d-flex align-items-center justify-content-center">
                                    <span class="badge bg-{{ $class }}-subtle text-{{ $class }}">
                                        {{ $rate }}%
                                    </span>
                                    <div class="progress ms-2" style="width: 60px; height: 6px;">
                                        <div class="progress-bar bg-{{ $class }}" style="width: {{ min($rate, 100) }}%"></div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">
                            <i class="ri-phone-line fs-3 d-block mb-2"></i>
                            @lang('translation.leads.no_calls_found')
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>
