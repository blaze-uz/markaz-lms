@extends('layouts.master')
@section('title')
@lang("translation.permissions.add")
@stop
@section('content')
{{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}
<form action="{{ route('admin.permissions.store') }}" method="POST">
    @csrf
    <div class="card">
        @include("admin.permissions.partials.form")
    </div>
    <div class="mb-5">
        <div class="d-flex flex-wrap gap-2 justify-content-end">
            <x-button id="detach-btn" class="btn-success">
                @lang("translation.btn.save")
            </x-button>
            <a href="{{ route("admin.permissions.index") }}" type="button" class="btn btn-warning">@lang("translation.btn.back")</a>
        </div>
    </div>
</form>
@endsection