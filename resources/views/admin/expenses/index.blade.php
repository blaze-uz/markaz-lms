@extends('layouts.master')
@section('title')
@lang('translation.expenses.expenses')
@endsection
@section('css')
<link rel="stylesheet" href="{{ asset('assets/libs/tom-select/tom-select.min.css') }}" />
@endsection
@section('content')
{{ Diglactic\Breadcrumbs\Breadcrumbs::render() }}
<livewire:expense.expense-list />
@endsection
@section('script-top')
<script src="{{ asset('assets/libs/flatpickr/flatpickr.min.js') }}"></script>
@endsection
