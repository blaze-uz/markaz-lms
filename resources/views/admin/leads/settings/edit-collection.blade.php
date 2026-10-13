@extends('layouts.master')
@section('title')
    @lang('translation.leads.edit_collection')
@endsection
@section('content')
    {{ Diglactic\Breadcrumbs\Breadcrumbs::render() }}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">@lang('translation.leads.edit_collection')</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.leads.settings.update-collection', $item->id) }}" method="POST">
                @csrf
                @method('PUT')
                @include('admin.leads.settings.partials.collection-form')
                <div class="text-end">
                    <button type="submit" class="btn btn-primary float-right">
                        @lang('translation.btn.save')
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
