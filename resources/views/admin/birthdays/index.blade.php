@extends('layouts.master')
@section('title')
@lang('translation.dashboard')
@endsection
@section('content')
{{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}
<livewire:dashboard.birthday-table />
@endsection