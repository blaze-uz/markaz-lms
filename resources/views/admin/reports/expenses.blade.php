@extends('layouts.master')
@section('title')
    @lang('translation.reports.expenses_report')
@endsection

@section('content')
    {{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}
    <livewire:reports.expenses-report />
@endsection

