@extends('layouts.master')
@section('title')
@lang('translation.sms.send')
@endsection
@section('content')
{{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}

@if ($errors->any())
<div class="alert alert-danger">
    <ul>
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<livewire:sms.send-sms :receivers="$receivers" :sendSmsType="$send_sms_type" />

@endsection
