@extends('layouts.master')
@section('title')
    @lang("translation.exams.add")
@stop
@section('content')
    {{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}
    <form action="{{ route('admin.exams.store') }}" method="POST">
        @csrf
        @include("admin.exams.partials.form")
        <div class="mb-5">
            <div class="d-flex flex-wrap gap-2 justify-content-end">
                <a href="{{ route("admin.exams.index") }}" type="button"
                   class="btn btn-warning">
                    @lang("translation.btn.back")
                </a>
                <x-button id="detach-btn" class="btn-success">
                    @lang("translation.btn.save")
                </x-button>
            </div>
        </div>
    </form>
@endsection