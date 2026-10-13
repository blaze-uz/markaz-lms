@extends('layouts.master')
@section('title')
    @lang("translation.leads.reminders")
@endsection
@section("content")
    {{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}
    <livewire:leads.reminders-list/>
@endsection
