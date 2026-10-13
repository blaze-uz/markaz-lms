@extends('layouts.master')
@section('title')
    @lang('translation.leads.calls')
@endsection
@section('content')
    {{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}
    <livewire:leads.calls-index>
@endsection