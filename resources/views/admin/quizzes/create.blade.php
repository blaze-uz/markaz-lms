@extends('layouts.master')
@section('title')
@lang("translation.quizzes.add")
@stop
@section('content')
{{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}
<form action="{{ route('admin.quizzes.store') }}" method="POST">
    @csrf
    <div class="card">
        @include("admin.quizzes.partials.form")
    </div>
    <div class="mb-5">
        <div class="flex-wrap gap-2 d-flex justify-content-end">
            <a href="{{ route('admin.quizzes.index') }}" type="button" class="btn btn-warning">
                @lang("translation.btn.back")
            </a>
            <x-button id="detach-btn" class="btn-success">
                @lang("translation.btn.save")
            </x-button>
        </div>
    </div>
</form>
@endsection