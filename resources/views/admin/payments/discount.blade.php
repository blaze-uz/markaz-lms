@extends('layouts.master')
@section('title')
    @lang('translation.payments.discounts')
@endsection
@section('content')
    {{ Diglactic\Breadcrumbs\Breadcrumbs::render() }}
    <livewire:payment.discount-list>
@endsection
