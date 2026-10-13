<div class="card rounded-4 card-height-100">
    <div class="card-header rounded-top-4">
        <h4 class="card-title mb-0">@lang('translation.sms.channel_statistics')</h4>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-centered table-nowrap mb-0">
                <thead class="table-light">
                    <tr>
                        <th>@lang('translation.sms.channel')</th>
                        <th>@lang('translation.sms.total')</th>
                        <th>@lang('translation.sms.sent')</th>
                        <th>@lang('translation.sms.pending')</th>
                        <th>@lang('translation.sms.failed')</th>
                        <th>@lang('translation.sms.success_rate')</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($statistics as $stat)
                        <tr>
                            <td>
                                @if ($stat->channel === 'playmobile')
                                    <i class="fas fa-mobile-alt text-primary me-2"></i>
                                @elseif($stat->channel === 'eskiz')
                                    <i class="fas fa-paper-plane text-info me-2"></i>
                                @endif
                                {{ ucfirst($stat->channel ?? __('translation.sms.no_channel')) }}
                            </td>
                            <td>{{ $stat->total_count }}</td>
                            <td class="text-success">{{ $stat->sent_count }}</td>
                            <td class="text-warning">{{ $stat->pending_count }}</td>
                            <td class="text-danger">{{ $stat->failed_count }}</td>
                            <td>
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar bg-success" role="progressbar"
                                        style="width: {{ $stat->success_rate }}%;"
                                        aria-valuenow="{{ $stat->success_rate }}" aria-valuemin="0" aria-valuemax="100">
                                        {{ $stat->success_rate }}%
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">@lang('translation.no_data')</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
