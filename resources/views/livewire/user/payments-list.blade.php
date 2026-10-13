<div>
    @push('css')
        <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css"/>
    @endpush
    <div class="row">
        <div class="col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body cursor-pointer">
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title bg-light text-success rounded-circle fs-3">
                            <i class="ri-money-dollar-circle-fill align-middle"></i>
                        </span>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-uppercase fw-semibold fs-12 text-muted mb-1">@lang("attr.balance")</p>
                            <h4 class="mb-0 text-success">{{money($balance)}}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body cursor-pointer">
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title bg-light text-success rounded-circle fs-3">
                            <i class="ri-money-dollar-circle-fill align-middle"></i>
                        </span>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-uppercase fw-semibold fs-12 text-muted mb-1">@lang("attr.income")</p>
                            <h4 class="mb-0 text-success">{{money($income)}}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body cursor-pointer">
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title bg-light text-danger rounded-circle fs-3">
                            <i class="ri-money-dollar-circle-fill align-middle"></i>
                        </span>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-uppercase fw-semibold fs-12 text-muted mb-1">@lang("attr.expense")</p>
                            <h4 class="mb-0 text-danger">{{money($expense)}}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="d-flex justify-content-end gap-2">
        <div class="d-flex justify-content-end mb-3 gap-3">
            <div>
                <button type="button" class="btn btn-primary mr-3 border text-nowrap" id="date-range"
                        data-livewire="@this">
                    <i class="far fa-calendar-alt"></i>
                    <span>
                        {{ $date ? $date : "Select" }}
                    </span>
                    <i class="fas fa-caret-down"></i>
                </button>
            </div>
            <select class="form-select form-control" wire:model.live="filter">
                <option value="all">@lang("translation.payments.all")</option>
                <option value="income">@lang("translation.payments.income")</option>
                <option value="expense">@lang("translation.payments.expense")</option>
            </select>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <tr>
                        <th>ID</th>
                        <th>@lang("translation.transfers.from")</th>
                        <th>@lang("translation.transfers.to")</th>
                        <th>@lang("translation.payments.type-name")</th>
                        <th>@lang("translation.students.payment_amount")</th>
                        <th>@lang("attr.created_at")</th>
                        <th></th>
                    </tr>
                    @forelse($transactions as $transaction)
                        <tr>
                            <td>{{ $transaction->id }}</td>
                            <td>
                                {{ $transaction->from_account->name ?? '-'}}
                                @switch($transaction->from_account?->account_code )
                                    @case(\App\Enums\AccountEnum::MAIN->value)
                                        <span class="badge bg-primary-subtle text-primary">
                                            @lang('attr.main_account')
                                        </span>
                                        @break
                                    @case(\App\Enums\AccountEnum::STAFF->value)
                                        <span class="badge bg-warning-subtle text-warning">
                                            @lang('attr.staff_account')
                                        </span>
                                        @break
                                    @case(\App\Enums\AccountEnum::GROUP->value)
                                        <span class="badge bg-success-subtle text-success">
                                            @lang('attr.group_account')
                                        </span>
                                        @break
                                    @case(\App\Enums\AccountEnum::STUDENT->value)
                                        <span class="badge bg-danger-subtle text-danger">
                                            @lang('attr.student_account')
                                        </span>
                                        @break
                                    @default
                                        <span class="badge bg-info-subtle text-info">
                                            @lang('attr.other_account')
                                        </span>
                                @endswitch
                            </td>
                            <td>
                                {{ $transaction->to_account->name ?? '-'}}
                                @switch($transaction->to_account?->account_code )
                                    @case(\App\Enums\AccountEnum::MAIN->value)
                                        <span class="badge bg-primary-subtle text-primary">
                                            @lang('attr.main_account')
                                        </span>
                                        @break
                                    @case(\App\Enums\AccountEnum::STAFF->value)
                                        <span class="badge bg-warning-subtle text-warning">
                                            @lang('attr.staff_account')
                                        </span>
                                        @break
                                    @case(\App\Enums\AccountEnum::GROUP->value)
                                        <span class="badge bg-success-subtle text-success">
                                            @lang('attr.group_account')
                                        </span>
                                        @break
                                    @case(\App\Enums\AccountEnum::STUDENT->value)
                                        <span class="badge bg-danger-subtle text-danger">
                                            @lang('attr.student_account')
                                        </span>
                                        @break
                                    @default
                                        <span class="badge bg-info-subtle text-info">
                                            @lang('attr.other_account')
                                        </span>
                                @endswitch
                            </td>
                            <td>
                                @if($transaction->to_account_id == auth()->user()->account_id)
                                    <span class="badge bg-success-subtle text-success">
                                        @lang("translation.payments.income")
                                    </span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger">
                                        @lang("translation.payments.expense")
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span @class([
                                            'fw-bold',
                                            'text-success' => $transaction->to_account_id == auth()->user()->account_id,
                                            'text-danger' => $transaction->to_account_id != auth()->user()->account_id,
                                    ])>
                                {{ money($transaction->amount) }}
                                </span>
                            </td>
                            <td>{{ dateFormat($transaction->created_at) }}</td>
                            <td></td>
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
                {{ $transactions->links() }}
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
        <script src="{{ asset('assets/js/jquery-3.7.1.min.js') }}"></script>
        <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
        <script type="text/javascript"
                src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
        <script>
            //set locale moment
            moment.locale('{{app()->getLocale() == "ru" ? "ru" : "uz-latn"}}');
            //Date range as a button
            $('#date-range').daterangepicker(
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
                    eval($('#date-range').attr('data-livewire')).set('date', selected);
                    $('#date-range span').html(selected);
                }
            )
        </script>
    @endpush
</div>
