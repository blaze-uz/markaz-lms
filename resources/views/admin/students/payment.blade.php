@extends('layouts.master')
@section('title')
    @lang('translation.students.payment')
@stop
@section('css')
    <style>
        /* .border-left-radius-0 {
                    border-top-left-radius: 0 !important;
                    border-bottom-left-radius: 0 !important;
                } */

        .border-right-radius-0 {
            border-top-right-radius: 0 !important;
            border-bottom-right-radius: 0 !important;
            line-height: 1.2 !important;
        }
    </style>
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endsection
@section('content')
    {{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}
    <form action="{{ route('admin.students.payment', $item->id) }}" method="POST">
        @csrf
        <div class="row">
            <div class="col-md-1"></div>
            <div class="col-md-10 row">
                <div class="col-md-7 col-sm-12">
                    <div class="card card-height-100">
                        <div class="card-body">
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            <div class="mb-3">
                                <label for="studentGroupPayment">
                                    @lang('translation.students.student')
                                </label>
                                <div class="p-2 rounded bg-secondary-subtle">
                                    <a href="{{ route('admin.students.show', $item->id) }}" class="text-reset">
                                        {{ $item->getFullName() }} | {{ $item->getFormattedPhone() }}
                                    </a>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="studentGroupPayment">
                                    @lang('translation.students.balance')
                                </label>
                                <div class="p-2 rounded bg-secondary-subtle">
                                    {{ money($item->account->getBalance()) }}
                                </div>
                            </div>
                            @if ($unpaidMonths->count() > 0)
                                <input type="hidden" name="debt" value="1">
                                <div class="mb-3 form-group">
                                    <label for="unpaid-month">
                                        @lang('translation.students.select_debt')
                                    </label>
                                    <select class="form-control @error('month') is-invalid @enderror" id="unpaid-month"
                                        name="month">
                                        <option value="0" selected>{{ __('translation.choose') }}</option>
                                        @foreach ($unpaidMonths as $month)
                                            <option value="{{ $month->id }}">
                                                @if ($month->group?->deleted_at == null)
                                                    {{ $month->group->teacher?->name ?? 'Teacher not found' }}
                                                    - {{ $month->group->name ?? 'Group not found' }}
                                                    @if ($month->group?->payment_type == 'date_to_date')
                                                        @php
                                                            $start = explode('/', $month->range)[0];
                                                            if (strlen($start) > 9) {
                                                                $date = \Carbon\Carbon::parse($start);
                                                            } else {
                                                                $date = now()->format('d.m.Y');
                                                            }
                                                        @endphp
                                                        ({{ dateFormat($date, 'F Y') }})
                                                    @elseif ($month->group?->payment_type == 'one_time')
                                                        ({{ __('translation.groups.one_time') }})
                                                    @else
                                                        ({{ dateFormat($month->created_at, 'F Y') }})
                                                    @endif
                                                    |
                                                    {{ money($month->left_amount) }}
                                                @else
                                                    <del>
                                                        (@lang('translation.groups.deleted'))
                                                        {{ $month->group->teacher->name }} - {{ $month->group->name }}
                                                        ({{ dateFormat($month->created_at, 'F Y') }})
                                                        | {{ money($month->left_amount) }}
                                                    </del>
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('month')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            @endif
                            <x-money-input :label="__('translation.students.payment_amount')" name="amount" :placeholder="__('translation.students.payment_amount')" :value="old('amount') ?? ''" />
                            <div class="gap-2 mb-3 d-flex">
                                <div class="gap-2 d-flex">
                                    @forelse($paymentTextSuggestions as $suggestion)
                                        <div class="mr-2 btn-group" role="group" aria-label="First group">
                                            <button type="button"
                                                class="text-black cursor-pointer badge btn bg-primary-subtle fw-normal text-suggestion-btn">{{ $suggestion->text }}</button>
                                            <button type="button"
                                                class="badge btn bg-danger-subtle text-danger text-suggestion-delete"
                                                data-id="{{ $suggestion->id }}"><i
                                                    class="cursor-pointer fa-solid fa-times text-danger"></i>
                                            </button>
                                        </div>
                                    @empty
                                        <span class="text-muted">
                                            @lang('translation.payments.click_for_add_suggestion') <i class="fa-solid fa-arrow-right"></i>
                                        </span>
                                    @endforelse
                                </div>
                                @if ($paymentTextSuggestions->count() < 5)
                                    <div class="px-1 rounded cursor-pointer bg-info-subtle" data-bs-toggle="modal"
                                        data-bs-target="#paymentTextSuggestionModal">
                                        <i class="fa-solid fa-plus fw-bold"></i>
                                    </div>
                                @endif
                            </div>

                            @if ($unpaidMonths->count() > 0)
                                <div class="flex-wrap gap-3 d-flex align-items-center">
                                    <!-- discount switcher -->
                                    <div class="mb-3 form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" id="discountSwitch">
                                        <label class="form-check-label" for="discountSwitch">
                                            @lang('translation.students.discount')
                                        </label>
                                    </div>
                                    @can('payment-update-data')
                                        <!-- date switcher -->
                                        <div class="mb-3 form-check form-switch">
                                            <input class="form-check-input" type="checkbox" role="switch" id="dateSwitch">
                                            <label class="form-check-label" for="dateSwitch">
                                                @lang('translation.students.payment_date_switch')
                                            </label>
                                        </div>
                                    @endcan
                                    @can('student-update-debt-amount')
                                        <!-- change debt switcher -->
                                        <div class="mb-3 form-check form-switch">
                                            <input class="form-check-input" type="checkbox" role="switch" id="debtSwitch">
                                            <label class="form-check-label" for="debtSwitch">
                                                @lang('translation.students.change_debt')
                                            </label>
                                        </div>
                                    @endcan
                                </div>
                            @endif
                            <div id="discount-div" class="d-none">
                                <div class="mb-3 form-group">
                                    <div class="gap-2 mb-2 d-flex align-items-center">
                                        <label class="m-0">@lang('translation.students.discount_account')</label>
                                        <button type="button" class="btn btn-soft-info btn-icon btn-sm rounded-circle"
                                            data-bs-toggle="modal" data-bs-target="#discountInfoModal"
                                            data-bs-toggle="tooltip" data-bs-placement="top" title="@lang('translation.students.discount_instructions')">
                                            <i class="ri-question-line"></i>
                                        </button>
                                    </div>
                                    <div class="flex-wrap gap-2 d-flex">
                                        <button type="button" class="btn btn-primary active" data-value="both"
                                            data-type="discount_type">
                                            @lang('translation.students.discount_both')
                                        </button>
                                        <button type="button" class="btn btn-light" data-value="main"
                                            data-type="discount_type">
                                            @lang('translation.students.only_main')
                                        </button>
                                        <button type="button" class="btn btn-light" data-value="teacher"
                                            data-type="discount_type">
                                            @lang('translation.students.only_teacher')
                                        </button>
                                        <button type="button" class="btn btn-light" data-value="other"
                                            data-type="discount_type">
                                            @lang('translation.students.other_account')
                                        </button>
                                    </div>
                                    <input type="hidden" name="discount_type" id="discount_type" value="both">
                                    <div id="other-account-select" class="mt-2 d-none">
                                        <select class="form-select" name="discount_account_id">
                                            <option value="">@lang('translation.choose_account')</option>
                                            @foreach ($discountAccounts as $account)
                                                <option value="{{ $account->id }}">{{ $account->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <x-money-input :label="__('translation.students.discount_amount')" name="discount_amount" :placeholder="__('translation.students.discount_amount')"
                                    :value="old('discount_amount') ?? ''" />
                            </div>
                            <div class="mb-3 form-group d-none" id="date-container">
                                <label for="paid_at">@lang('translation.students.payment_date')</label>
                                <input type="date" id="paid_at" name="date"
                                    class="form-control @error('date') is-invalid @enderror">
                                @error('date')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            @can('student-update-debt-amount')
                                <div class="mb-3 form-group d-none" id="debt-container">
                                    <x-money-input name="debt_amount" :label="__('translation.students.debt_amount')" value="" />
                                    @error('debt_amount')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                    <div id="debtAmountNotice"></div>
                                </div>
                            @else
                                <input type="hidden" id="debt_amount" name="debt_amount" value="">
                            @endcan
                        </div>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="card">
                        <div class="card-body">
                            <label for="">{{ __('translation.payments.choose_payment_type') }}</label>
                            <div class="flex-wrap gap-2 d-flex justify-content-start">
                                @foreach ($paymentTypes as $key => $paymentType)
                                    <div class="mr-3">
                                        <input type="radio" class="btn-check" name="payment_type_id"
                                            id="payment-type-{{ $paymentType->id }}" value="{{ $paymentType->id }}"
                                            @if ($key == 0) checked @endif>
                                        <label class="btn btn-outline-primary" for="payment-type-{{ $paymentType->id }}">
                                            <i class="mdi mdi-cash-multiple"></i>
                                            {{ $paymentType->name }}
                                        </label>
                                    </div>
                                @endforeach
                                @error('payment_type_id')
                                    <div class="text-danger">* {{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <div class="mb-3 form-group">
                                <label for="comment">@lang('translation.students.comment')</label>
                                <textarea name="comment" id="comment" class="form-control @error('comment') is-invalid @enderror" rows="3">{{ old('comment') }}</textarea>
                                <div class="flex gap-2 mt-3">
                                    @forelse($commentTextSuggestions as $suggestion)
                                        <div class="mr-2 btn-group" role="group" aria-label="First group">
                                            <button type="button"
                                                class="text-black cursor-pointer badge btn bg-primary-subtle fw-normal comment-suggestion-btn">{{ $suggestion->text }}</button>
                                            <button type="button"
                                                class="badge btn bg-danger-subtle text-danger comment-suggestion-delete"
                                                data-id="{{ $suggestion->id }}"><i
                                                    class="cursor-pointer fa-solid fa-times text-danger"></i>
                                            </button>
                                        </div>
                                    @empty
                                        <span class="text-muted">
                                            @lang('translation.payments.click_for_add_comment_suggestion')
                                            <i class="fa-solid fa-arrow-right"></i>
                                        </span>
                                    @endforelse
                                    @if ($commentTextSuggestions->count() < 5)
                                        <span class="px-1 rounded cursor-pointer bg-info-subtle" data-bs-toggle="modal"
                                            data-bs-target="#paymentCommentSuggestionModal">
                                            <i class="fa-solid fa-plus fw-bold"></i>
                                        </span>
                                    @endif
                                </div>
                                @error('comment')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mb-3 d-flex justify-content-between">
                    <span class="text-muted">
                        {{ __('translation.sms.payment_sms_status') }}:
                        <a href="{{ route('admin.settings.sms') }}">
                            @if ($paymentSmsStatus == 'enable' || $paymentSmsStatus == '1')
                                <span class="text-success">{{ __('attr.enable') }}</span>
                            @else
                                <span class="text-danger">{{ __('attr.disable') }}</span>
                            @endif
                        </a>
                    </span>
                    <x-button id="detach-btn" class="btn-primary">
                        <i class="mdi mdi-cash-multiple"></i>
                        @lang('translation.students.pay')
                    </x-button>
                </div>
            </div>
            <div class="col-md-1"></div>
        </div>
    </form>

    <!-- Quick payment text suggestion create modal -->
    <div class="modal fade" id="paymentTextSuggestionModal" tabindex="-1" aria-labelledby="paymentTextSuggestionLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="paymentTextSuggestionLabel">
                        @lang('translation.payments.quick_payment_suggestion')
                    </h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.text-suggestions.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="type" value="payment">
                        <x-money-input :label="__('translation.students.payment_amount')" name="text" :placeholder="__('translation.students.payment_amount')" :value="$payment->amount ?? (old('amount') ?? '')" />
                        @error('text')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            @lang('translation.Close')
                        </button>
                        <button type="submit" class="btn btn-primary">
                            @lang('translation.btn.save')
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Discount Info Modal -->
    <div class="modal fade" id="discountInfoModal" tabindex="-1" aria-labelledby="discountInfoModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="discountInfoModalLabel">@lang('translation.students.discount_instructions')
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="discount-info">
                        <!-- Both accounts -->
                        <div class="gap-2 mb-4 d-flex align-items-start">
                            <div class="avatar-xs">
                                <div class="rounded avatar-title bg-primary">
                                    <i class="ri-team-line"></i>
                                </div>
                            </div>
                            <div>
                                <h6 class="mb-1">@lang('translation.students.both_accounts')</h6>
                                <p class="mb-0 text-muted">
                                    @lang('translation.students.both_accounts_description')
                                    <br>
                                    <small class="mt-2 text-muted d-block">
                                        <i class="align-middle ri-information-line me-1"></i>
                                        @lang('translation.students.example'):
                                        <span class="fw-medium">100 000 so'm</span> @lang('translation.students.discount') ➜
                                        <span class="text-success fw-medium">60 000 so'm</span>
                                        <small>(@lang('translation.students.main'))</small> +
                                        <span class="text-info fw-medium">40 000 so'm</span>
                                        <small>(@lang('translation.students.teacher'))</small>
                                    </small>
                                </p>
                            </div>
                        </div>

                        <!-- Main Account -->
                        <div class="gap-2 mb-4 d-flex align-items-start">
                            <div class="avatar-xs">
                                <div class="rounded avatar-title bg-success">
                                    <i class="ri-bank-line"></i>
                                </div>
                            </div>
                            <div>
                                <h6 class="mb-1">@lang('translation.students.main_account')</h6>
                                <p class="mb-0 text-muted">
                                    @lang('translation.students.main_account_description')
                                </p>
                            </div>
                        </div>

                        <!-- Teacher Account -->
                        <div class="gap-2 mb-4 d-flex align-items-start">
                            <div class="avatar-xs">
                                <div class="rounded avatar-title bg-info">
                                    <i class="ri-user-2-line"></i>
                                </div>
                            </div>
                            <div>
                                <h6 class="mb-1">@lang('translation.students.teacher_account')</h6>
                                <p class="mb-0 text-muted">
                                    @lang('translation.students.teacher_account_description')
                                </p>
                            </div>
                        </div>

                        <!-- Other Account -->
                        <div class="gap-2 d-flex align-items-start">
                            <div class="avatar-xs">
                                <div class="rounded avatar-title bg-warning">
                                    <i class="ri-user-3-line"></i>
                                </div>
                            </div>
                            <div>
                                <h6 class="mb-1">@lang('translation.students.other_account')</h6>
                                <p class="mb-0 text-muted">
                                    @lang('translation.students.other_account_description')
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick payment text suggestion create modal -->
    <div class="modal fade" id="paymentCommentSuggestionModal" tabindex="-1"
        aria-labelledby="paymentCommentSuggestionLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="paymentCommentSuggestionLabel">
                        @lang('translation.payments.quick_payment_comment_suggestion')
                    </h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.text-suggestions.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="type" value="payment_comment">
                        <div class="form-group">
                            <label for="paymentComment">@lang('translation.students.payment_comment')</label>
                            <input type="text" id="paymentComment" name="text"
                                class="form-control @error('text') is-invalid @enderror">
                            @error('text')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            @lang('translation.Close')
                        </button>
                        <button type="submit" class="btn btn-primary">
                            @lang('translation.btn.save')
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection
@section('script')
    <script src='{{ asset('assets/js/jquery-3.7.1.min.js') }}'></script>
    <script>
        $(document).ready(function() {
            $('#discountSwitch').change(function() {
                if ($(this).is(':checked')) {
                    //remove d-none class
                    $('#discount-div').removeClass('d-none');
                } else {
                    $('#discount-div').addClass('d-none');
                }
            });
            $('#dateSwitch').change(function() {
                if ($(this).is(':checked')) {
                    $('#date-container').removeClass('d-none');
                } else {
                    $('#date-container').addClass('d-none');
                }
            });
            $('#debtSwitch').change(function() {
                if ($(this).is(':checked')) {
                    $('#debt-container').removeClass('d-none');
                } else {
                    $('#debt-container').addClass('d-none');
                }
            });
            //if unpaid month is change then change debt amount
            $('#unpaid-month').change(function() {
                let monthId = $(this).val();
                let url = "{{ route('admin.students.get-student-group-payment', ':id') }}";
                url = url.replace(':id', monthId);
                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(response) {
                        $('#debt_amount').val(response.amount);
                        //if left amount is not equal to amount then console text
                        if (response.left_amount != response.amount) {
                            let paidAmount = response.amount - response.left_amount;
                            $('#debtAmountNotice').html(
                                `<div class="alert alert-warning">Qarzdorlikning ${paidAmount} uzs miqdori to'langan</div>`
                            );
                        }
                    }
                });
            });

            $('.text-suggestion-btn').click(function() {
                $('#amount').val($(this).text());
            });

            $('.text-suggestion-delete').click(function() {
                let parentDiv = $(this).parent();
                let textSuggestion = $(this).data('id');
                let url = "{{ route('admin.text-suggestions.delete', ':id') }}";
                url = url.replace(':id', textSuggestion);
                $.ajax({
                    url: url,
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function() {
                        parentDiv.addClass('d-none');
                        console.log("deleted!");
                    },
                    context: this,
                });
            });

            $('.comment-suggestion-btn').click(function() {
                $('#comment').val($(this).text());
            });

            $('.comment-suggestion-delete').click(function() {
                let parentDiv = $(this).parent();
                let textSuggestion = $(this).data('id');
                let url = "{{ route('admin.text-suggestions.delete', ':id') }}";
                url = url.replace(':id', textSuggestion);

                if (confirm("Ishonchingiz komilmi?")) {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });

                    $.ajax({
                        url: url,
                        type: 'DELETE',
                        success: function(response) {
                            parentDiv.remove();
                            toastr.success("@lang('translation.common.deleted_successfully')");
                        },
                        error: function(xhr) {
                            toastr.error(xhr.responseJSON?.message || "@lang('translation.common.something_went_wrong')");
                        }
                    });
                }
            });

            $('button[data-type="discount_type"]').click(function() {
                // Remove active class from all buttons
                $('button[data-type="discount_type"]').removeClass('btn-primary active').addClass(
                    'btn-light');
                // Add active class to clicked button
                $(this).removeClass('btn-light').addClass('btn-primary active');

                const type = $(this).data('value');
                $('#discount_type').val(type);

                // Handle other account selection visibility
                if (type === 'other') {
                    $('#other-account-select').removeClass('d-none');
                } else {
                    $('#other-account-select').addClass('d-none');
                    $('select[name="discount_account_id"]').val('');
                }
            });
        });
    </script>
@endsection
