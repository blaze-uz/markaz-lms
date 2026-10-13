@extends('layouts.master')
@section('title')
    @lang('translation.dashboard')
@endsection
@section('content')
    <div class="mb-3 d-flex justify-content-between align-items-center">
        <h3>Markaz LMS 🚀</h3>
        <a href="{{ route('admin.calendar.view') }}" class="btn btn-primary bg-info-subtle d-flex " style="border: none;">
            <span class="d-flex align-items-center">
                <i class="fas fa-calendar-alt me-2 text-white"></i>
                <p class="text-white m-0 p-0 align-middle">@lang('translation.calendar')</p>
            </span>
        </a>
    </div>

    <livewire:dashboard.dashboard-cards />
    <livewire:dashboard.birthday-card />
    <div class="mt-3 row">
        <div class="col-md-6 col-sm-12">
            <livewire:dashboard.attendance-chart />
            <livewire:dashboard.students-chart />
        </div>
        <div class="col-md-6 col-sm-12">
            <div class="row">
                <div class="col-md-12">
                    <livewire:dashboard.students-come-out-statistics />
                </div>
                <div class="col-md-6">
                    <livewire:dashboard.students-gender-chart />
                </div>
                <div class="col-md-6">
                    <livewire:student.students-financial-status-chart />
                </div>
            </div>
        </div>
    </div>
@endsection
