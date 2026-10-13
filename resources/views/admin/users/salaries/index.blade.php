@extends('layouts.master')
@section('title')
    {{__('translation.users.salaries')}}
@endsection
@section('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
@endsection
@section('content')
    {{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}
    <livewire:user.salaries-table/>
@endsection
