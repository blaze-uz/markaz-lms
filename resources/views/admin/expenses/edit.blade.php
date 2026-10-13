@extends('layouts.master')
@section('title')
@lang('translation.expenses.edit')
@endsection
@section('css')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet" />
@endsection
@section('content')
{{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}
<form action="{{ route('admin.expenses.update', $expense) }}" method="POST">
    @csrf
    @method('PUT')
    @include('admin.expenses.partials.form')
    <div class="d-flex justify-content-end">
        <x-button id="detach-btn" class="btn-primary">
            @lang("translation.expenses.edit")
        </x-button>
    </div>
</form>
@endsection