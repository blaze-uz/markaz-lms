@extends('layouts.master')
@section('title')
@lang("translation.users.add")
@stop
@section('content')
{{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}
<form action="{{ route('admin.users.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="card">
        @include("admin.users.partials.form")
    </div>
    <div class="mb-5">
        <div class="d-flex justify-content-between align-items-center">
            <div class="alert alert-warning">
                <i class="fas fa-warning"></i>
                Hozircha o'qituvchilar bilan kelishuv faqat foiz ko'rinishida!
            </div>
            <div class="d-flex flex-wrap gap-2 justify-content-end">
                <x-button id="detach-btn" class="btn-success">
                    @lang("translation.btn.save")
                </x-button>
                <a href="{{ route("admin.users.index") }}" type="button" class="btn btn-warning">@lang("translation.btn.back")</a>
            </div>
        </div>
    </div>
</form>
@endsection