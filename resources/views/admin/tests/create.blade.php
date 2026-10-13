@extends('layouts.master')
@section('title')
@lang("translation.tests.add")
@stop
@section('content')
{{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}
@if ($errors->any())
<div class="alert alert-danger">
    <ul>
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif
<form action="{{ route('admin.quizzes.store-test', $quiz_id) }}" method="POST">
    @csrf
    <div class="card">
        <div class="card-body">
            @include("admin.tests.partials.form")
        </div>
    </div>
    <div class="mb-5">
        <div class="flex-wrap gap-2 d-flex justify-content-end">
            <x-button id="detach-btn" class="btn-success">
                @lang("translation.btn.save")
            </x-button>
        </div>
    </div>
</form>
@endsection