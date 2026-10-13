@extends('layouts.master')
@section('title')
    @lang("translation.leads.leads")
@endsection
@section("content")
    {{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}
    <livewire:leads.leads-table/>
@endsection