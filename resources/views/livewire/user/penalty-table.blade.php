<div>
    <div class="d-flex justify-content-between align-items-baseline mb-3">
        <h5 class="card-title mb-3">
            @lang('translation.users.penalties')
        </h5>
        <div>
            <button type="button" class="btn btn-default border" id="penalties-date-picker" data-livewire="@this">
                <i class="far fa-calendar-alt"></i>
                <span>
                {{ $date ? $date : "Select" }}
            </span>
                <i class="fas fa-caret-down"></i>
            </button>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table">
            <tr>
                <th>ID</th>
                <th>@lang("attr.amount")</th>
                <th>@lang("attr.description")</th>
                <th>@lang("attr.date")</th>
                @can('user-delete-penalty')
                    <th>@lang("translation.actions")</th>
                @endcan
            </tr>
            @forelse($penalties as $penalty)
                <tr>
                    <td>{{ $penalty->id }}</td>
                    <td>{{ money($penalty->amount) }}</td>
                    <td>{{ $penalty->description ?? '-' }}</td>
                    <td>{{ dateFormat($penalty->date) }}</td>
                    @can('user-delete-penalty')
                        <td>
                            <form action="{{route('admin.users.delete-penalty', $penalty->id)}}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm"
                                        onclick="return confirm('Are you sure?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    @endcan
                </tr>
            @empty
                <tr class="text-center">
                    <td colspan="100">@lang("translation.no_data")</td>
                </tr>
            @endforelse
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
            moment.locale('{{app()->getLocale() == "ru" ? "ru" : "uz-latn"}}');
            //Date range as a button
            $('#penalties-date-picker').daterangepicker(
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
                    eval($('#penalties-date-picker').attr('data-livewire')).set('date', selected);
                    $('#penalties-date-picker span').html(selected);
                }
            )
        </script>
    @endpush
</div>
