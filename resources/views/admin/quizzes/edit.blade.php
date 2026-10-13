@extends('layouts.master')
@section('title')
@lang("translation.quizzes.edit")
@stop
@section('content')
{{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}
<form action="{{ route('admin.quizzes.update', $item->id) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="card">
        @include("admin.quizzes.partials.form")
    </div>
    <div class="mb-5">
        <div class="flex-wrap gap-2 d-flex justify-content-end">
            <a href="{{ route('admin.quizzes.show', $item->id) }}" type="button" class="btn btn-warning">
                @lang("translation.quizzes.go_to_show")
            </a>
            <x-button id="detach-btn" class="btn-success">
                @lang("translation.btn.save")
            </x-button>
        </div>
    </div>
</form>
@endsection