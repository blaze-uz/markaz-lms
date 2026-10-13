@extends('layouts.master')
@section('title')
SMS
@endsection
@section('content')
{{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}
<div class="mb-3 d-flex justify-content-end">
    <a href="{{ route('admin.sms.batches') }}" class="btn btn-soft-info">
        <i class="ri-stack-line align-bottom me-1"></i>
        SMS batchlar
    </a>
</div>
<livewire:sms.sms-table />
@endsection
