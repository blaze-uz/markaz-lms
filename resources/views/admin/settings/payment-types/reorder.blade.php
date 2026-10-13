@extends('layouts.master')
@section('title')
    {{ __('translation.reorder') }}
@endsection
@section('content')
    {{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}
    <livewire:settings.payment-types-reorder-table/>
@endsection
