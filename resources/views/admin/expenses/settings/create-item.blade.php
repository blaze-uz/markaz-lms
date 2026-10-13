@extends('layouts.master')
@section('title')
@lang('translation.expenses.expenses')
@endsection
@section('content')
{{ Diglactic\Breadcrumbs\Breadcrumbs::render() }}
<div class="card">
    <div class="card-header">
        <h3 class="card-title">@lang('translation.expenses.add_item')</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.expenses.settings.store-item') }}" method="POST">
            @csrf
            @include('admin.expenses.settings.partials.item-form')
            <x-button id="detach-btn" class="btn-sm btn-primary float-right">
                @lang("translation.btn.save")
            </x-button>
        </form>
    </div>
</div>
@endsection