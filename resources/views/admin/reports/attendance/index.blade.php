@extends('layouts.master')

@section('title', __('translation.reports.attendance'))

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">{{ __('translation.reports.attendance') }}</h4>
            </div>
        </div>
    </div>
    <livewire:report.attendance-report />
@endsection
