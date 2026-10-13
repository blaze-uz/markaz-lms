<div>
    @push('css')
        <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/daterangepicker.min.css') }}" />
    @endpush
    <div class="mb-3 d-flex justify-content-end">
        <div class="gap-2 d-flex">
            <div class="filter-item">
                <button type="button" class="bg-white border btn w-100" id="daterange-btn-sms" data-livewire="@this">
                    <i class="far fa-calendar-alt"></i>
                    <span>
                        {{ $date ?: __('translation.select_date') }}
                    </span>
                    <i class="fas fa-caret-down"></i>
                </button>
            </div>
        </div>
    </div>

    {{-- Cards: Total SMS, Sent SMS, Pending SMS, Failed SMS, Scheduled SMS --}}
    <livewire:report.sms.cards :start-date="$startDate" :end-date="$endDate" lazy />

    {{-- SMS Statistics Chart --}}
    <livewire:report.sms.statistics :start-date="$startDate" :end-date="$endDate" />

    <div class="row">
        <div class="col-lg-6">
            {{-- SMS Template Statistics --}}
            <livewire:report.sms.template-statistics :start-date="$startDate" :end-date="$endDate" />
        </div>
        <div class="col-lg-6">
            {{-- SMS Channel Statistics --}}
            {{-- <livewire:report.sms.channel-statistics :start-date="$startDate" :end-date="$endDate" /> --}}
            {{-- Daily Spending Component --}}
            <livewire:report.sms.daily-spending :start-date="$startDate" :end-date="$endDate" />
        </div>
    </div>
</div>

@push('custom-scripts')
    <script type="text/javascript" src="{{ asset('assets/js/moment.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/js/daterangepicker.min.js') }}"></script>
    <script>
        $(function() {
            let startDate = moment().startOf('month');
            let endDate = moment().endOf('month');

            if (@this.date) {
                let dates = @this.date.split(' - ');
                startDate = moment(dates[0]);
                endDate = moment(dates[1]);
            }

            $('#daterange-btn-sms').daterangepicker({
                ranges: {
                    "{{ __('attr.today') }}": [moment(), moment()],
                    "{{ __('attr.yesterday') }}": [moment().subtract(1, 'days'), moment().subtract(1,
                        'days')],
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
                startDate: startDate,
                endDate: endDate,
                locale: {
                    "customRangeLabel": "{{ __('attr.optionalDate') }}",
                    "applyLabel": "{{ __('attr.save') }}",
                    "cancelLabel": "{{ __('attr.cancel') }}",
                }
            }, function(start, end) {
                let selected = start.format('YYYY.MM.DD') + ' - ' + end.format('YYYY.MM.DD');
                @this.date = selected;
                @this.startDate = start.format('YYYY-MM-DD');
                @this.endDate = end.format('YYYY-MM-DD');
                $('#daterange-btn-sms span').html(selected);
                window.location.href =
                    `{{ route('admin.reports.sms') }}?from=${start.format('YYYY-MM-DD')}&to=${end.format('YYYY-MM-DD')}`;
            });
        });
    </script>
@endpush
</div>
