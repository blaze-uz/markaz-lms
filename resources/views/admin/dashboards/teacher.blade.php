@extends('layouts.master')
@section('title')
@lang('translation.dashboard')
@endsection
@section('content')
<div class="mb-4">
    <h3>
        {{ __("translation.greetings") }} {{ auth()->user()->name }} 👋
    </h3>
</div>
<livewire:dashboard.teacher-cards />
<div class="row">
    <div class="col-lg-4 col-sm-12">
        <div class="mb-3">
            <livewire:dashboard.teacher-lessons />
        </div>
        @can('student-show')
        <livewire:dashboard.students-financial-status-chart />
        @endcan
    </div>

    <div class="col-lg-8 col-sm-12">
        <livewire:dashboard.teacher-students-chart />
        @can('student-show')
        <livewire:user.user-student-payments-table />
        @endcan
    </div>
</div>
@endsection