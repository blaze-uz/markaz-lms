@extends('layouts.master')
@section('title')
    @lang("translation.groups.add")
@stop
@section('content')
    {{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}
    <form action="{{ route('admin.groups.store') }}" method="POST" enctype="multipart/form-data" id="create-group-form">
        @csrf
        @include("admin.groups.partials.form")
        <div class="mb-5">
            <div class="d-flex flex-wrap gap-2 justify-content-end">
                <x-button id="detach-btn" class="btn-success">
                    @lang("translation.btn.save")
                </x-button>
                <a href="{{ route("admin.groups.index") }}" type="button"
                   class="btn btn-warning">@lang("translation.btn.back")</a>
            </div>
        </div>
    </form>
@endsection