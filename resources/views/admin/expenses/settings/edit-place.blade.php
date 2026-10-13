@extends('layouts.master')
@section('title')
    @lang('translation.expenses.edit_place')
@endsection
@section('content')
    {{ Diglactic\Breadcrumbs\Breadcrumbs::render() }}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">@lang('translation.expenses.edit_place')</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.expenses.settings.edit-place', $item->id) }}" method="POST">
                @csrf
                @method('PUT')
                @include('admin.expenses.settings.partials.place-form')
                <button type="submit" class="btn btn-sm btn-primary float-right">
                    @lang('translation.btn.save')
                </button>
            </form>
        </div>
    </div>
@endsection
