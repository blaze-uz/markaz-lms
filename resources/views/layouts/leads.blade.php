@extends('layouts.master')
@section('title')
@lang("translation.leads.leads")
@endsection
@section("content")
    @inertia
@endsection

@section('script')
    @routes
    @vite(['resources/js/leads.js'])
    @inertiaHead
@endsection