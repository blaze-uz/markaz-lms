<div>
    @push('css')
        <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css"/>
    @endpush
    @include('admin.leads.calls.partials.cards')
    <div class="card">
        <div class="card-header">
            <div class="d-flex align-items-baseline justify-content-between">
                <x-search parentClass="m-0"/>
                <div class="d-flex gap-2 align-items-baseline">
                    <label>
                        <select wire:model.live="statusFilter" class="form-control form-select">
                            <option value="all">@lang("translation.leads.all_calls")</option>
                            <option value="answered">@lang("translation.leads.answered")</option>
                            <option value="declined">@lang("translation.leads.declined")</option>
                        </select>
                    </label>
                    <button type="button" class="btn btn-primary border" id="daterange-btn-debts"
                            data-livewire="@this">
                        <i class="far fa-calendar-alt"></i>
                        <span>
                        {{ $date ? $date : "Select" }}
                    </span>
                        <i class="fas fa-caret-down"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <tr>
                        <th>ID</th>
                        <th>@lang("translation.leads.lead")</th>
                        <th>@lang("translation.leads.phone")</th>
                        <th>@lang("translation.leads.call_status")</th>
                        <th>@lang("translation.leads.causer")</th>
                        <th>@lang("translation.leads.call_date")</th>
                    </tr>
                    @forelse($items as $item)
                        <tr>
                            <td>{{ $item->id }}</td>
                            <td>{{ $item->lead?->name ?? '-' }}</td>
                            <td>
                                <a href="tel:{{ $item->lead?->phone ?? '-' }}">{{ $item->lead?->phone ?? '-' }}</a>
                            </td>
                            <td>
                                <span @class(['badge',
                                        'bg-danger-subtle text-danger' => $item->status == 'declined',
                                        'bg-success-subtle text-success' => $item->status == 'answered'])>
                                {{ __('translation.leads.'.$item->status) }}
                                </span>
                            </td>
                            <td>{{ $item->causer->name }}</td>
                            <td>{{ dateFormat($item->created_at, 'd.m H:i') }}</td>
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
            <div class="d-flex justify-content-end">
                {{ $items->links() }}
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
            $('#daterange-btn-debts').daterangepicker({
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
                    startDate: moment(),
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