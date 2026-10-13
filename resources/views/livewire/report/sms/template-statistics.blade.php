<div class="card rounded-4 card-height-100">
    <div class="card-header rounded-top-4">
        <h4 class="mb-0 card-title">@lang('translation.sms.template_statistics')</h4>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table mb-0 table-centered">
                <thead class="table-light">
                    <tr>
                        <th>@lang('translation.sms.template_name')</th>
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
                            <td>{{ $stat->template_name ?? __('translation.sms.no_template') }}</td>
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
