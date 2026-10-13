@extends("layouts.master")
@section("title")
@lang('translation.payments.payments')
@endsection
@section("content")
{{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}
<livewire:teacher-student-payments-table :teacher="auth()->user()" />
@endsection