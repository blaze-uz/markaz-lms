@extends('layouts.master')
@section('title')
@lang('translation.receipts.settings')
@endsection
@section('css')
<style>
    .receipt-preview {
        font-family: 'Courier New', Courier, monospace;
        font-size: 14px;
        background: white;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
</style>
@endsection

@section('content')
{{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}

<livewire:settings.receipt-settings />

@endsection
