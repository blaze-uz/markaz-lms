@extends('layouts.master')
@section('title')
    @lang('translation.telegram.messages')
@endsection
@section('content')
    {{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}

    <livewire:telegram.telegram-messages-table />

@endsection
