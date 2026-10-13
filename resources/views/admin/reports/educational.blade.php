@extends('layouts.master')
@section('title')
@lang("translation.reports.education")
@endsection
@section('css')
<link href="{{ asset('assets/libs/choices.js/choices.js.min.css') }}" rel="stylesheet" type="text/css">
<link href="{{ asset('assets/libs/flatpickr/flatpickr.min.css') }}" rel="stylesheet" type="text/css">
@endsection
@section('content')
{{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}
@php
    $from = request()->from;
    $to = request()->to;
    $branchId = request()->branch_id;
@endphp
<form action="{{ route('admin.reports.educational') }}">
    <div class="mb-3 d-flex justify-content-end">
        <div class="gap-2 d-flex">
            <div class="input-group">
                <input name="from" type="text" class="form-control" data-provider="flatpickr" data-date-format="Y-m-d"
                    placeholder="{{__('attr.from')}}" value="{{ old('from', request()->from) }}">
            </div>
            <div class="input-group">
                <input name="to" type="text" class="form-control" data-provider="flatpickr" data-date-format="Y-m-d"
                    placeholder="{{__('attr.to')}}" value="{{ old('to', request()->to) }}">
            </div>
            <div>
                <button type="submit" class="gap-2 btn btn-primary d-flex justify-content-between align-items-center">
                    <i class="fas fa-filter"></i>
                    @lang("translation.Filter")
                </button>
            </div>
        </div>
    </div>
</form>
{{-- Cards: Attendance, Groups, Students, Teachers, Deleted Students --}}
<livewire:report.education.cards :start-date="$from" :end-date="$to" lazy />
{{-- Attendance chart --}}
<livewire:report.attendance-chart :start-date="$from" :end-date="$to" lazy />
{{-- Homework chart --}}
<livewire:report.homework-chart :start-date="$from" :end-date="$to" lazy />
{{-- Homework report --}}
<livewire:report.homework-report :start-date="$from" :end-date="$to" lazy />
{{-- Students chart --}}
<livewire:report.students-chart :start-date="$from" :end-date="$to"
    :branch-id="$branchId" lazy />
{{-- Students chart by age --}}
<div class="row">
    <div class="col-md-6 col-sm-12">
        <livewire:report.group-attendance :branch-id="$branchId" lazy />
    </div>
    <div class="col-md-6 col-sm-12">
        <livewire:report.group-attendance-exist :branch-id="$branchId" lazy />
    </div>
</div>
<div class="row">
    <div class="col-md-5 col-sm-12">
        <livewire:dashboard.students-gender-chart :branch-id="$branchId" lazy />
    </div>
    <div class="col-md-7 col-sm-12">
        <livewire:report.students-age-chart :branch-id="$branchId" lazy />
    </div>
</div>
{{-- Telegram Connection Statistics (Collapsible) --}}
<livewire:report.telegram-connection-stats lazy />
@endsection
@section('script-bottom')
<script src="{{ asset('assets/js/apex/apex.min.js') }}"></script>
@endsection
@section('script-top')
<script src="{{ asset('assets/libs/flatpickr/flatpickr.min.js') }}"></script>
@endsection
