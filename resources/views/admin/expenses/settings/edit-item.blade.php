@extends('layouts.master')
@section('title')
    @lang('translation.expenses.edit_item')
@endsection
@section('content')
    {{ Diglactic\Breadcrumbs\Breadcrumbs::render() }}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">@lang('translation.expenses.edit_item')</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.expenses.settings.update-item', $item->id) }}" method="POST">
                @csrf
                @method('PUT')
                @include("admin.expenses.settings.partials.item-form")
                <button type="submit" class="btn btn-sm btn-primary float-right">
                    @lang('translation.btn.save')
                </button>
            </form>
        </div>
    </div>
@endsection
