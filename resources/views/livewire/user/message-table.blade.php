<div>
    <div class="d-flex justify-content-between align-items-baseline mb-3">
        <h5 class="card-title mb-3">
            @lang('translation.users.messages')
        </h5>
        <div>
            <button type="button" class="btn btn-default border" id="messages-date-picker" data-livewire="@this">
                <i class="far fa-calendar-alt"></i>
                <span>
                    {{ $date ? $date : 'Select' }}
                </span>
                <i class="fas fa-caret-down"></i>
            </button>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>@lang('translation.telegram.message')</th>
                    <th>@lang('translation.sms.send_type')</th>
                    <th>@lang('translation.users.sent_by')</th>
                    <th>@lang('attr.date')</th>
                </tr>
            </thead>
            <tbody>
                @forelse($messages as $message)
                    <tr>
                        <td>{{ $message->id }}</td>
                        <td>
                            <div class="text-break" style="max-width: 400px;">
                                {!! $message->message !!}
                            </div>
                        </td>
                        <td>
                            @if($message->send_type === 'telegram')
                                <span class="badge bg-info">
                                    <i class="mdi mdi-telegram"></i> Telegram
                                </span>
                            @elseif($message->send_type === 'sms')
                                <span class="badge bg-success">
                                    <i class="mdi mdi-message-text"></i> SMS
                                </span>
                            @else
                                <span class="badge bg-secondary">-</span>
                            @endif
                        </td>
                        <td>
                            {{ $message->createdBy?->name ?? '-' }}
                        </td>
                        <td>{{ dateFormat($message->created_at) }}</td>
                    </tr>
                @empty
                    <tr class="text-center">
                        <td colspan="5">@lang('translation.no_data')</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-preloader d-none" wire:loading.class.remove="d-none">
        <div class="card-status">
            <div class="spinner-border text-success">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    </div>
    @push('custom-scripts')
        <script>
            //set locale moment
            moment.locale('{{ app()->getLocale() == 'ru' ? 'ru' : 'uz-latn' }}');
            //Date range as a button
            $('#messages-date-picker').daterangepicker(
                {
                    ranges: {
                        "{{ __('attr.today') }}": [moment(), moment()],
                        "{{ __('attr.yesterday') }}": [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                        "{{ __('attr.last7days') }}": [moment().subtract(6, 'days'), moment()],
                        "{{ __('attr.last30days') }}": [moment().subtract(29, 'days'), moment()],
                        "{{ __('attr.thisMonth') }}": [moment().startOf('month'), moment().endOf('month')],
                        "{{ __('attr.lastMonth') }}": [
                            moment().subtract(1, 'months').startOf('month'),
                            moment().subtract(1, 'months').endOf('month')
                        ],
                        "{{ __('attr.thisYear') }}": [moment().startOf('year'), moment().endOf('year')],
                        "{{ __('attr.lastYear') }}": [
                            moment().subtract(1, 'years').startOf('year'),
                            moment().subtract(1, 'years').endOf('year')
                        ],
                    },
                    startDate: moment().subtract(29, 'days'),
                    endDate: moment(),
                    locale: {
                        "customRangeLabel": "{{ __('attr.optionalDate') }}",
                        "applyLabel": "{{ __('attr.save') }}",
                        "cancelLabel": "{{ __('attr.cancel') }}",
                    }
                },
                function (start, end) {
                    let selected = start.format('YYYY.MM.DD') + ' - ' + end.format('YYYY.MM.DD');
                    eval($('#messages-date-picker').attr('data-livewire')).set('date', selected);
                    $('#messages-date-picker span').html(selected);
                }
            )
        </script>
    @endpush
</div>

