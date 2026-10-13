@extends('layouts.master')

@section("title")
{{ $item->name }}
@stop
@section('css')
<link href="{{ asset('assets/libs/choices.js/choices.js.min.css') }}" rel="stylesheet" type="text/css">
<link href="{{ asset('assets/libs/flatpickr/flatpickr.min.css') }}" rel="stylesheet" type="text/css">
@endsection
@section('content')
<!-- Group Cards -->
{{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}

<div class="row">
    <!-- Group Info Card -->
    <div class="col-md-4">
        <div class="card">
            <div class="p-4 card-body">
                <div>
                    <div class="mx-auto d-flex justify-content-center w-100"></div>
                    <div class="mt-4 text-center">
                        <h5 class="mb-1">
                            {{ $item->name }}
                        </h5>
                        <p class="text-muted">@lang("attr.create_date") {{ dateFormat($item->created_at) }}</p>
                    </div>
                    <div class="table-responsive">
                        <table class="table mb-0 table-borderless">
                            <tbody>

                                <tr>
                                    <th><span class="fw-medium">@lang("translation.accounts.balance")</span></th>
                                    <td>{{ money($item->getBalance()) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-between">
                        <a class="btn btn-warning" href="{{ route('admin.accounts.index') }}">
                            <span class="mdi mdi-arrow-left"></span>
                            @lang("translation.btn.back")
                        </a>
                        <a href="{{ route('admin.accounts.transaction-view', $item) }}" class="btn btn-primary">
                            <span class="mdi mdi-cash-multiple"></span>
                            @lang("translation.accounts.add_transaction")
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <!--end card-->
    </div>

    <!-- Right info card -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <!-- Nav tabs -->
                <ul class="mb-3 nav nav-tabs nav-tabs-custom nav-primary nav-justified" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link active" data-bs-toggle="tab" href="#transactions" role="tab"
                            aria-selected="true">
                            @lang("translation.transactions")
                        </a>
                    </li>
                </ul>

                <!-- Tab panes -->
                <div class="tab-content">
                    <div class="tab-pane active show" id="transactions" role="tabpanel">
                        <div class="table-responsive">
                            @if($transactions->count())
                            <table class="table">
                                <tr>
                                    <th>ID</th>
                                    <th>@lang('attr.from_account')</th>
                                    <th>@lang('attr.to_account')</th>
                                    <th>@lang('attr.type')</th>
                                    <th>@lang("attr.amount")</th>
                                    <th>@lang("attr.comment")</th>
                                    <th>@lang("attr.created_at")</th>
                                </tr>
                                @foreach($transactions as $transaction)
                                <tr>
                                    <td>{{ $transaction->id }}</td>
                                    <td>
                                        @if($transaction->from_account)
                                        @if($transaction->from_account_id == $item->id)
                                        {{ $transaction->from_account->name }}
                                        @else
                                        <a href="{{ route('admin.accounts.show', $transaction->from_account_id) }}">
                                            {{ $transaction->from_account->name }} <i class="mdi mdi-open-in-new"></i>
                                        </a>
                                        @endif
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
                                        @else
                                        -
                                        @endif
                                    </td>
                                    <td>
                                        @if($transaction->to_account)
                                        @if($transaction->to_account_id == $item->id)
                                        {{ $transaction->to_account->name }}
                                        @else
                                        <a href="{{ route('admin.accounts.show', $transaction->to_account_id) }}">
                                            {{ $transaction->to_account->name }} <i class="mdi mdi-open-in-new"></i>
                                        </a>
                                        @endif
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
                                        @else
                                        -
                                        @endif
                                    </td>
                                    <td>
                                        @if($transaction->to_account_id == $item->id)
                                        <span class="badge bg-success-subtle text-success">
                                            @lang('attr.income')
                                        </span>
                                        @elseif($transaction->from_account_id == $item->id)
                                        <span class="badge bg-danger-subtle text-danger">
                                            @lang('attr.expense')
                                        </span>
                                        @endif
                                    </td>
                                    <td class="text-nowrap">
                                        <span @class([ 'fw-bold' , 'text-success'=> $transaction->to_account_id ==
                                            $item->id,
                                            'text-danger' => $transaction->from_account_id == $item->id
                                            ])>
                                            {{ money($transaction->amount ?? 0) }}
                                        </span>
                                    </td>
                                    <td>{{ $transaction->description ?? '-' }}</td>
                                    <td>{{ dateFormat($transaction->created_at) }}</td>
                                </tr>
                                @endforeach
                            </table>
                            {{ $transactions->links() }}
                            @else
                            <x-no-result />
                            @endif
                        </div>
                    </div>
                </div>
            </div><!-- end card-body -->
        </div>
    </div>
</div>
@stop
@section('script')
<script src="{{ asset('assets/libs/choices.js/choices.js.min.js') }}"></script>
<script>
    // Get today's date as a string in the format "YYYY-MM-DD"
    let today = new Date().toISOString().slice(0, 10);

    // Set the value of the date input field to today's date
    document.getElementById("date").value = today;
</script>
@endsection
