<div>
    @push('css')
        <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css"/>
    @endpush
    <div class="d-flex justify-content-between align-items-center gap-2 mb-3 w-100">
        <button type="button" class="btn btn-defaul mr-3 border" id="daterange-btn" data-livewire="@this">
            <i class="far fa-calendar-alt"></i>
            <span>
                {{ $date ? $date : "Select" }}
            </span>
            <i class="fas fa-caret-down"></i>
        </button>
    </div>
    <div class="table-responsive">
        <table class="table">
            <tr>
                <th>ID</th>
                <th>@lang("translation.students.full_name")</th>
                <th>@lang("translation.students.payment_amount")</th>
                <th>@lang("translation.students.payment_date")</th>
                <th></th>
            </tr>
            @forelse($transactions as $transaction)
                <tr>
                    <td>{{$transaction->id}}</td>
                    <td class="text-nowrap">
                        {{$transaction->from_account->name}}
                    </td>
                    <td class="text-nowrap">{{money($transaction->amount)}}</td>
                    <td class="text-nowrap">{{dateFormat($transaction->created_at)}}</td>
                    
                    <td style="width: 50px">
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="100">
                        <x-no-result/>
                    </td>
                </tr>
            @endforelse
        </table>
    </div>
    {{$transactions->links()}}
    <div class="card-preloader d-none" wire:loading.class.remove="d-none">
        <div class="card-status">
            <div class="spinner-border text-success">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    </div>
    @push('custom-scripts')
        <script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
        <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
        <script type="text/javascript"
                src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
        <script>
            //set locale moment
            moment.locale('{{app()->getLocale() == "ru" ? "ru" : "uz-latn"}}');
            //Date range as a button
            $('#daterange-btn').daterangepicker(
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
                    eval($('#daterange-btn').attr('data-livewire')).set('date', selected);
                    $('#daterange-btn span').html(selected);
                }
            )
        </script>
    @endpush
</div>
