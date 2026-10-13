@extends('layouts.master')
@section('title')
@lang('translation.leads.add_collection')
@endsection
@section('content')
{{ Diglactic\Breadcrumbs\Breadcrumbs::render() }}
<div class="card">
    <div class="card-header">
        <h3 class="card-title">@lang('translation.leads.add_collection')</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.leads.settings.store-collection') }}" method="POST">
            @csrf
            @include('admin.leads.settings.partials.collection-form')
            <div class="text-end">
                <x-button id="detach-btn" class="btn-primary float-end">
                    @lang("translation.btn.save")
                </x-button>
            </div>
        </form>
    </div>
</div>
@endsection