@extends('layouts.master')
@section('title')
SMS batchlar
@endsection
@section('content')
{{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}
<div class="mb-3 d-flex justify-content-end">
    <a href="{{ route('admin.sms.index') }}" class="btn btn-soft-secondary">
        <i class="ri-arrow-left-line align-bottom me-1"></i>
        SMS ro'yxati
    </a>
</div>
<livewire:sms.sms-batches-table />
@endsection
