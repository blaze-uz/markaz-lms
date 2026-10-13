<div>
    @push('css')
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    @endpush
    <div class="d-flex justify-content-between align-items-baseline mb-3">
        <h5 class="card-title mb-3">
            @lang('translation.students.payments')
        </h5>
        <div>
            <button type="button" class="btn btn-default border" id="daterange-btn" data-livewire="@this">
                <i class="far fa-calendar-alt"></i>
                <span>
                    {{ $date ? $date : "Select" }}
                </span>
                <i class="fas fa-caret-down"></i>
            </button>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead class="bg-primary-subtle">
                <tr>
                    <th>ID</th>
                    <th>@lang('attr.from_account')</th>
                    <th>@lang('attr.to_account')</th>
                    <th>@lang('attr.type')</th>
                    <th>@lang("attr.amount")</th>
                    <th>@lang("attr.created_at")</th>
                    @can('payment-delete')
                    <th>@lang('translation.actions')</th>
                    @endcan
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $transaction)
                <tr>
                    <td>{{ $transaction->id }}</td>
                    <td class="text-nowrap">
                        @if($transaction->from_account)
                        <a href="{{ route('admin.accounts.show', $transaction->from_account->id) }}">
                            {{ $transaction->from_account->name }} <i class="mdi mdi-open-in-new"></i>
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
                        </a>
                        @else
                        -
                        @endif
                    </td>
                    <td class="text-nowrap">
                        @if($transaction->to_account)
                        <a href="{{ route('admin.accounts.show', $transaction->to_account->id) }}">
                            {{ $transaction->to_account->name }} <i class="mdi mdi-open-in-new"></i>
                            @switch($transaction->to_account->account_code )
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
                        </a>
                        @else
                        -
                        @endif
                    </td>
                    <td>
                        @if($transaction->to_account_id == $user->account->id)
                        <span class="badge bg-success-subtle text-success">
                            @lang('attr.income')
                        </span>
                        @elseif($transaction->from_account_id == $user->account->id)
                        <span class="badge bg-danger-subtle text-danger">
                            @lang('attr.expense')
                        </span>
                        @endif
                    </td>
                    <td class="text-nowrap">
                        <span @class([ 'fw-bold' , 'text-success'=> $transaction->to_account_id == $user->account->id,
                            'text-danger' => $transaction->from_account_id == $user->account->id
                            ])>
                            {{ money($transaction->amount ?? 0) }}
                        </span>
                    </td>
                    <td>{{ dateFormat($transaction->created_at) }}</td>

                    @can('payment-delete')
                    <td>
                        <form
                            action="{{route('admin.users.payments.delete', ['user' => $user->id, 'payment' => $transaction->id])}}"
                            method="POST">
                            @csrf
                            @method('DELETE')
                            <!-- confirm button with js-->
                            <button type="submit" class="btn bg-danger-subtle text-danger btn-sm"
                                onclick="return confirm('Confirm deleting payment!')">
                                <i class="mdi mdi-delete"></i>
                                @lang('translation.btn.delete')
                            </button>
                        </form>
                    </td>
                    @endcan
                </tr>
                @empty
                <tr>
                    <td colspan="100">
                        <x-no-result />
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="d-flex justify-content-end">
        {{ $transactions->links() }}
    </div>
    <div class="card-preloader d-none" wire:loading.class.remove="d-none">
        <div class="card-status">
            <div class="spinner-border text-success">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    </div>
    @push('custom-scripts')
    <script src="{{ asset('assets/js/jquery-3.7.1.min.js') }}"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
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
