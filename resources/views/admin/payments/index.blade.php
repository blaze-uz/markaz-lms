@extends("layouts.master")
@section("title")
    @lang("translation.payments.payments")
@endsection
@section("content")
    {{ Diglactic\Breadcrumbs\Breadcrumbs::render() }}
    <livewire:payment.payment-list>
@endsection
