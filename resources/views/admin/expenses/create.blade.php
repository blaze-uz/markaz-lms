@extends('layouts.master')
@section('title')
@lang('translation.expenses.add')
@endsection
@section('css')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet" />
@endsection
@section('content')
{{ Diglactic\Breadcrumbs\Breadcrumbs::render() }}
<form action="{{ route('admin.expenses.store') }}" method="POST">
    @csrf
    @include('admin.expenses.partials.form')
    <div class="d-flex justify-content-end">
        <x-button id="detach-btn" class="btn-primary">
            @lang('translation.expenses.add')
        </x-button>
    </div>
</form>
@endsection