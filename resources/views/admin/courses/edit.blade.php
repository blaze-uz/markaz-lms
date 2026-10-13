@extends('layouts.master')
@section('title')
    @lang('translation.courses.edit')
@stop
@section('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endsection
@section('content')
    {{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}
    <form action="{{ route('admin.courses.update', $item->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="card">
            @include('admin.courses.partials.form')
        </div>
        <div class="mb-5">
            <div class="hstack gap-2 justify-content-end">
                <x-button id="detach-btn" class="btn-success">
                    @lang('translation.btn.save')
                </x-button>
                <a href="{{ route('admin.courses.index') }}" type="button" class="btn btn-warning">@lang('translation.btn.back')</a>
            </div>
        </div>
    </form>
@endsection
