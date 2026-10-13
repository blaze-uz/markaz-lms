@extends("layouts.master")
@section("title")
@lang("translation.payments.payments")
@endsection
@section("content")
<div>
    @push('css')
    <style>
        * {
            color: #000 !important;
        }

        .receipt_header {
            padding-bottom: 20px;
            border-bottom: 1px dashed #000;
            text-align: center;
        }

        .receipt_header h1 {
            font-size: 20px;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        .receipt_header h1 span {
            display: block;
            font-size: 25px;
        }

        .receipt_header h2 {
            font-size: 14px;
            font-weight: 300;
        }

        .receipt_header h2 span {
            display: block;
        }

        table {
            width: 100%;
        }

        thead,
        tfoot {
            position: relative;
        }

        thead th:not(:last-child) {
            text-align: left;
        }

        thead th:last-child {
            text-align: right;
        }

        thead::after {
            content: '';
            width: 100%;
            border-bottom: 1px dashed #000;
            display: block;
            position: absolute;
        }

        tbody td:not(:last-child),
        tfoot td:not(:last-child) {
            text-align: left;
        }

        tbody td:last-child,
        tfoot td:last-child {
            text-align: right;
        }

        tbody tr:first-child td {
            padding-top: 15px;
        }

        tbody tr:last-child td {
            padding-bottom: 15px;
        }

        tfoot tr:first-child td {
            padding-top: 15px;
        }

        tfoot::before {
            content: '';
            width: 100%;
            border-top: 1px dashed #000;
            display: block;
            position: absolute;
        }

        tfoot tr:first-child td:first-child,
        tfoot tr:first-child td:last-child {
            font-weight: bold;
            font-size: 20px;
        }

        .date_time_con {
            display: flex;
            justify-content: center;
            column-gap: 25px;
        }

        .items {
            margin-top: 25px;
        }

        .receipt_container {
            width: 300px;
        }

        span {
            font-weight: 500;
        }
    </style>
    @endpush
    <div>
        <div id="divToPrint">
            <div class="card receipt_container">
                <div class="card-body">
                    @if($receiptSettings->show_logo && $receiptSettings->logo_position === 'top' && $logo)
                        <div class="mb-3 d-flex justify-content-center">
                            <img src="{{ $logo }}" alt="logo" width="150" class="mb-2">
                        </div>
                    @endif

                    <div class="receipt_header">
                        <h1 class="mb-3">To'lov kvitansiyasi</h1>
                        @if($receiptSettings->show_header_text)
                            <h2>{!! nl2br(e($receiptSettings->header_text)) !!}</h2>
                        @endif
                    </div>

                    <div class="receipt_body">
                        <div class="date_time_con">
                            <div class="date">
                                {{ isset($item->date) ? dateFormat($item->date) : '-' }}
                            </div>
                            <div class="time">
                                {{ isset($item->date) ? dateFormat($item->date, 'H:i') : '-' }}
                            </div>
                        </div>

                        <div class="items">
                            @if($receiptSettings->show_payment_id)
                                <div class="mb-2 d-flex justify-content-between">
                                    <span>ID</span>
                                    <span>{{ $item->id ?? '-' }}</span>
                                </div>
                            @endif

                            @if($receiptSettings->show_student_info)
                                <div class="mb-2 d-flex justify-content-between">
                                    <span>@lang("translation.students.student")</span>
                                    <span>{{ $item->student?->getFullName() ?? '-' }}</span>
                                </div>
                            @endif

                            @if($receiptSettings->show_group_info && isset($item->student_group_payment->group))
                                <div class="mb-2 d-flex justify-content-between">
                                    <span>@lang("translation.groups.group")</span>
                                    <span>{{ $item->student_group_payment->group->name ?? '-' }}</span>
                                </div>
                                <div class="mb-2 d-flex justify-content-between">
                                    <span>@lang("translation.groups.debt_range")</span>
                                    <span>{{ $item->student_group_payment->range ?? '-' }}</span>
                                </div>
                                <div class="mb-2 d-flex justify-content-between">
                                    <span>@lang("translation.students.teacher")</span>
                                    <span>{{ $item->student_group_payment->group->teacher->name ?? '-' }}</span>
                                </div>
                            @endif

                            @if($receiptSettings->show_payment_type)
                                <div class="mb-2 d-flex justify-content-between">
                                    <span>@lang("translation.payments.payment_type")</span>
                                    <span>
                                        @if(isset($item->student_group_payment->group))
                                            {{ __("translation.payments.for_debt") }}
                                        @else
                                            {{ __("translation.payments.for_balance") }}
                                        @endif
                                    </span>
                                </div>
                                <div class="mb-2 d-flex justify-content-between">
                                    <span>@lang("translation.payments.form_of_payment")</span>
                                    <span>{{ $item->payment_type->name ?? '-' }}</span>
                                </div>
                            @endif

                            @if($receiptSettings->show_cashier)
                                <div class="mb-2 d-flex justify-content-between">
                                    <span>@lang("translation.payments.created_by")</span>
                                    <span>{{ $item->creator->name ?? '-' }}</span>
                                </div>
                            @endif

                            @if(isset($item->comment) && $item->comment)
                                <div class="mb-2 d-flex justify-content-between">
                                    <span>@lang("translation.payments.comment")</span>
                                    <span>{{ $item->comment }}</span>
                                </div>
                            @endif

                            @if($receiptSettings->show_amount)
                                <div class="d-flex justify-content-between">
                                    <h5 class="d-inline-block fw-semibold">@lang("translation.students.payment_amount")</h5>
                                    <h4 class="d-inline-block fw-semibold">{{ isset($item->amount) ? money($item->amount) : '-' }}</h4>
                                </div>
                            @endif
                        </div>
                    </div>

                    @if($receiptSettings->show_logo && $receiptSettings->logo_position === 'bottom' && $logo)
                        <div class="mb-3 d-flex justify-content-center">
                            <img src="{{ $logo }}" alt="logo" width="150" class="mt-3 mb-2">
                        </div>
                    @endif

                    @if($receiptSettings->show_footer && $receiptSettings->footer_text)
                        <div class="mt-4 text-center">
                            {!! nl2br(e($receiptSettings->footer_text)) !!}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        document.body.innerHTML = document.getElementById('divToPrint').innerHTML;
        window.print();
        setTimeout(function () {
            window.location.href = "{{ route('admin.students.show', $item->student_id ?? 0) }}";
        }, 1000);
    });
</script>
@endsection