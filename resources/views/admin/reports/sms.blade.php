@extends('layouts.master')
@section('title')
@lang("translation.reports.sms")
@endsection
@section('css')
<link href="{{ asset('assets/css/daterangepicker.min.css') }}" rel="stylesheet" type="text/css">
@endsection
@section('content')
{{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}
<livewire:report.sms.sms-report />
@endsection