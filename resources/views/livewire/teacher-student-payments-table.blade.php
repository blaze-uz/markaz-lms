<div>
    @push('css')
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    @endpush

    <div class="flex-wrap d-flex justify-content-between align-items-baseline">
        <div class="flex-wrap gap-2 d-flex justify-content-between align-items-center w-100">
            <div class="gap-2 d-flex align-items-baseline">
                <x-search />
            </div>

            <div class="flex-wrap gap-2 d-flex">
                <button type="button" class="mr-3 bg-white border btn" id="daterange-btn-debts" data-livewire="@this">
                    <i class="far fa-calendar-alt"></i>
                    <span>
                        {{ $date ? $date : "Select" }}
                    </span>
                    <i class="fas fa-caret-down"></i>
                </button>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-hover">
                <tr>
                    <th>#</th>
                    <th>{{__('translation.students.full_name')}}</th>
                    <th>{{__('translation.students.phone')}}</th>
                    <th>{{__('translation.students.group')}}</th>
                    <th>{{__('attr.payment_type')}}</th>
                    <th>{{__('attr.amount')}}</th>
                    <th>{{__('attr.discount')}}</th>
                    <th>{{__('attr.payment_date')}}</th>
                </tr>
                @forelse($payments as $payment)
                <tr>
                    <td class="text-nowrap">
                        {{ ($payments->total() - (($payments->currentpage()-1) * $payments->perpage() + $loop->index))
                        }}
                    </td>
                    <td class="text-nowrap">
                        <a href="{{ route('admin.students.show', $payment->student->id ?? 0) }}" class="text-reset">
                            @if($payment->student?->deleted_at)
                            <del data-bs-toggle="tooltip"
                                title="{{__('translation.students.deleted')}}">{{$payment->student?->getFullName() ??
                                '-'}}</del>
                            @else
                            {{$payment->student?->getFullName() ?? '-'}}
                            @endif
                        </a>
                    </td>
                    <td class="text-nowrap">
                        @if($payment->student?->deleted_at)
                        <del data-bs-toggle="tooltip"
                            title="{{__('translation.students.deleted')}}">{{$payment->student?->getFormattedPhone() ??
                            '-'}}</del>
                        @else
                        <a href="tel:{{$payment->student?->getFormattedPhone() ?? '-'}}" class="text-reset">
                            {{$payment->student?->getFormattedPhone() ?? '-'}}
                        </a>
                        @endif
                    </td>
                    <td class="text-nowrap">
                        @if(!$payment->student_group_payment?->group)
                        <span class="badge bg-success-subtle text-success">
                            @lang("translation.students.payment_for_balance")
                        </span>
                        @endif
                        <a href="{{ route('admin.groups.show', $payment->student_group_payment->group->id ?? 0) }}"
                            class="text-reset">
                            {{ $payment->student_group_payment?->group?->name }}
                        </a>
                        <br>
                        <small class="text-muted">
                            {{ $payment->student_group_payment?->range }}
                        </small>
                    </td>
                    <td class="text-nowrap">
                        {{$payment->payment_type?->name ?? '-'}}<br>
                        @if($payment->refunds()->count())
                        <span class="badge bg-danger-subtle text-danger">
                            @lang("translation.students.refunded")
                        </span>
                        @endif
                    </td>
                    <td class="text-nowrap fw-bold text-success">{{money($payment->amount)}}</td>
                    <td @class(["text-nowrap fw-bold", "text-danger"=> $payment->payment_discounts->sum('amount') > 0,
                        "text-muted" => $payment->payment_discounts->sum('amount') == 0])>
                        {{money($payment->payment_discounts->sum('amount'))}}
                    </td>
                    <td class="text-nowrap">
                        <span class="fw-bold">
                            {{dateFormat($payment->date, "Y.m.d H:i")}}
                        </span><br>
                        <a href="{{route('admin.users.show', $payment->creator->id ?? 0)}}" class="text-muted">
                            @lang("attr.receiver"): {{$payment->creator?->name ?? '-'}}
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="100" class="text-center">
                        <x-no-result />
                    </td>
                </tr>
                @endforelse
            </table>
            {{$payments->links()}}
        </div>
        <div class="card-preloader d-none" wire:loading.class.remove="d-none">
            <div class="card-status">
                <div class="spinner-border text-success">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment modal -->
    {{-- <div wire:ignore.self class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="paymentModalLabel">@lang("translation.transaction")</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <table class="table">
                        <tr>
                            <th>ID</th>
                            <td>{{ $paymentModalItem?->id ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>@lang("translation.students.student")</th>
                            <td>{{ $paymentModalItem?->student?->getFullName() ?? '-' }}</td>
                        </tr>
                        @if($paymentModalItem?->student_group_payment?->group)
                        <tr>
                            <th>@lang("translation.groups.group")</th>
                            <td>
                                <a
                                    href="{{ route('admin.groups.show', $paymentModalItem?->student_group_payment->group->id ?? 0) }}">
                                    {{ $paymentModalItem?->student_group_payment->group?->name ?? '-' }}
                                </a><br>
                                <span class="text-muted">@lang("translation.groups.course"): </span>
                                {{ $paymentModalItem?->student_group_payment->group->course?->name ?? '-' }}
                            </td>
                        </tr>
                        <tr>
                            <th>@lang("translation.groups.debt_range")</th>
                            <td>{{ $paymentModalItem?->student_group_payment->range ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>@lang("translation.groups.teacher")</th>
                            <td>
                                <a
                                    href="{{ route('admin.users.show', $paymentModalItem?->student_group_payment->group->teacher_id ?? 0) }}">
                                    {{ $paymentModalItem?->student_group_payment->group->teacher?->name ?? '-' }}
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <th>@lang("translation.payments.form_of_payment")</th>
                            <td>{{ $paymentModalItem?->payment_type?->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>@lang("translation.payments.payment_type")</th>
                            <td>{{__('translation.payments.for_debt')}}</td>
                        </tr>
                        @else
                        <tr>
                            <th>@lang("translation.payments.payment_type")</th>
                            <td>{{__('translation.payments.for_balance')}}</td>
                        </tr>
                        @endif
                        <tr>
                            <th>@lang("translation.payments.form_of_payment")</th>
                            <td>{{ $paymentModalItem?->payment_type?->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>@lang("translation.payments.created_by")</th>
                            <td>
                                <a href="{{ route('admin.users.show', $paymentModalItem?->creator->id ?? 0) }}">
                                    {{ $paymentModalItem?->creator->name ?? '-' }}
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <th>@lang("translation.students.payment_amount")</th>
                            <td>{{ money($paymentModalItem?->amount ?? 0) }}</td>
                        </tr>
                        <tr>
                            <th>@lang("translation.payments.comment")</th>
                            <td>{{ $paymentModalItem?->comment ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>@lang("attr.payment_date")</th>
                            <td>{{ dateFormat($paymentModalItem?->created_at) }}</td>
                        </tr>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        @lang("translation.Close")
                    </button>
                    <a class="btn btn-primary" href="{{ route('admin.payments.print', $paymentModalItem?->id ?? 0) }}">
                        <i class="fas fa-print"></i>
                        Print
                    </a>
                </div>
            </div>
        </div>
    </div> --}}

    @push('custom-scripts')
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
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

            tippy('.send-sms-btn', {
                content: "{!!__('translation.payments.send_sms')!!}",
            });
            tippy('.edit-payment-btn', {
                content: "{!!__('translation.payments.edit')!!}",
            });
            tippy('.refund-payment-btn', {
                content: "{!!__('translation.payments.refund')!!}",
            });
    </script>
    @endpush
</div>