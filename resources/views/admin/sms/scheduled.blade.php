@extends('layouts.master')
@section('title')
Scheduled SMS
@endsection
@section('content')
{{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}
<livewire:sms.scheduled-table />
@endsection
