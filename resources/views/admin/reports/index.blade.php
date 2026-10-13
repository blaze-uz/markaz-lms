@extends('layouts.master')
@section('title')
    @lang('translation.reports.reports')
@endsection
@section('css')
    <link href="{{ asset('assets/libs/choices.js/choices.js.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/libs/flatpickr/flatpickr.min.css') }}" rel="stylesheet" type="text/css">
@endsection
@section('content')
    {{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}

    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div></div>
        <div class="d-flex align-items-center gap-3">
            <form action="{{ route('admin.reports.index') }}" class="d-flex gap-2">
                <div class="input-group">
                    <input name="from" type="text" class="form-control" data-provider="flatpickr"
                        data-date-format="Y-m-d" placeholder="{{ __('attr.from') }}"
                        value="{{ old('from', request()->from) }}">
                </div>
                <div class="input-group">
                    <input name="to" type="text" class="form-control" data-provider="flatpickr"
                        data-date-format="Y-m-d" placeholder="{{ __('attr.to') }}" value="{{ old('to', request()->to) }}">
                </div>
                <div>
                    <button type="submit" class="gap-2 btn btn-primary d-flex justify-content-between align-items-center">
                        <i class="fas fa-filter"></i>
                        @lang('translation.Filter')
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Cards: KIRIM, CHIQIM, XARAJAT, MAOSH, KASSA, Account balances --}}
    @include('admin.reports.partials.cards')
    {{-- Income and expense chart --}}
    @include('admin.reports.partials.payment-chart')
    {{-- Group balance statistics --}}
    <livewire:report.group-payment-statistics />
    {{-- Payments type cards --}}
    @include('admin.reports.partials.payment-type-cards')
    {{-- Teachers balance statistics --}}
    <livewire:report.teacher-balance-statistics />
    {{-- Group debt --}}
    <div class="row">
        <div class="col-md-6 col-sm-12">
            <livewire:report.group-debt lazy />
        </div>
        <div class="col-md-6 col-sm-12">
            @include('admin.reports.partials.payments-by-creators')
        </div>
    </div>
    {{-- Expenses Card --}}
    <livewire:report.expenses-card :start="request()->from" :end="request()->to" lazy />
    {{-- Top 5 expenses --}}
    <livewire:report.expenses :start="request()->from" :end="request()->to" />
@endsection
@section('script')
    <script src="{{ asset('assets/js/apex/apex.min.js') }}"></script>
@endsection
@section('script-top')
    <script src="{{ asset('assets/libs/flatpickr/flatpickr.min.js') }}"></script>
@endsection
