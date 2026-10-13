@extends('layouts.master')
@section('title')
@yield('inertia-title', config('app.name'))
@endsection
@section('css')
<style>
    .page-content { background-color: #f3f3f9 !important; }
</style>
@endsection
@section('content')
    {{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}
    @inertia
@endsection
@section('script')
    @routes
    @vite(['resources/js/app.js'])
    @inertiaHead
@endsection
