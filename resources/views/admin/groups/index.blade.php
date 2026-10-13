@extends('layouts.master')
@section('title')
    @lang('translation.groups.groups')
@endsection
@section('content')
    {{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}
    {{-- <livewire:group.groups-index/> --}}
    <livewire:group.groups-table/>
@endsection
