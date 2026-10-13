<div>
    @push('css')
        <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css"/>
    @endpush
    <div class="d-flex justify-content-between align-items-baseline flex-wrap">
        <div class="d-flex justify-content-between align-items-center gap-2 mb-3 w-100 flex-wrap">
            {{--            <x-search/>--}}
            <div>
                <h4 class="text-danger fw-semibold">
                    {{ money($discountsSum->sum('amount')) }}
                </h4>
            </div>
            <button type="button" class="btn btn-primary mr-3 border" id="daterange-btn-debts"
                    data-livewire="@this">
                <i class="far fa-calendar-alt"></i>
                <span>
                    {{ $date ? $date : "Select" }}
                </span>
                <i class="fas fa-caret-down"></i>
            </button>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <tr>
                        <th>ID</th>
                        <th>@lang("attr.from_account")</th>
                        <th>@lang("attr.to_account")</th>
                        <th>@lang("translation.students.discount_amount")</th>
                        <th>@lang("translation.students.date")</th>
                    </tr>
                    @foreach($discounts as $discount)
                        <tr>
                            <td>{{ $discount->id }}</td>
                            <td>
                                <a href="{{ route('admin.accounts.show', $discount->transaction->from_account_id ?? 0) }}">
                                    {{ $discount->transaction->from_account->name ?? '-' }}
                                    <i class="fas fa-external-link-alt"></i>
                                </a>
                            </td>
                            <td>
                                <a href="{{ route('admin.accounts.show', $discount->transaction->to_account_id ?? 0) }}">
                                    {{ $discount->transaction->to_account->name ?? '-' }}
                                    <i class="fas fa-external-link-alt"></i>
                                </a>
                            </td>
                            <td>{{ money($discount->amount) }}</td>
                            <td>{{ dateFormat($discount->created_at) }}</td>
                        </tr>
                    @endforeach
                </table>
                {{ $discounts->links() }}
            </div>
        </div>
        <div class="card-preloader d-none" wire:loading.class.remove="d-none">
            <div class="card-status">
                <div class="spinner-border text-success">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        </div>
    </div>
    @push('custom-scripts')
        <script type="text/javascript" src="{{ asset('assets/js/jquery-3.7.1.min.js') }}"></script>
        <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
        <script type="text/javascript"
                src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
        <script>
            //set locale moment
            moment.locale('{{app()->getLocale() == "ru" ? "ru" : "uz-latn"}}');
            //Date range as a button
            $('#daterange-btn-debts').daterangepicker(
                {
                    ranges: {
                        "{{__('attr.today')}}": [moment(), moment()],
                        "{{__('attr.yesterday')}}": [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                        "{{__('attr.last7days')}}": [moment().subtract(6, 'days'), moment()],
                        "{{__('attr.last30days')}}": [moment().subtract(29, 'days'), moment()],
                        "{{__('attr.thisMonth')}}": [moment().startOf('month'), moment().endOf('month')],
                        "{{__('attr.lastMonth')}}": [
                            moment().subtract(1, 'months').startOf('month'),
                            moment().subtract(1, 'months').endOf('month')
                        ],
                        "{{__('attr.thisYear')}}": [moment().startOf('year'), moment().endOf('year')],
                        "{{__('attr.lastYear')}}": [
                            moment().subtract(1, 'years').startOf('year'),
                            moment().subtract(1, 'years').endOf('year')
                        ],
                    },
                    startDate: moment().subtract(29, 'days'),
                    endDate: moment(),
                    locale: {
                        "customRangeLabel": "{{__('attr.optionalDate')}}",
                        "applyLabel": "{{__('attr.save')}}",
                        "cancelLabel": "{{__('attr.cancel')}}",
                    }
                },
                function (start, end) {
                    let selected = start.format('YYYY.MM.DD') + ' - ' + end.format('YYYY.MM.DD');
                    eval($('#daterange-btn-debts').attr('data-livewire')).set('date', selected);
                    $('#daterange-btn-debts span').html(selected);
                }
            )
        </script>
    @endpush
</div>
