@extends('layouts.master')
@section('title')
    @lang("translation.roles.add")
@stop
@section('content')
    {{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}
    <form action="{{ route('admin.roles.store') }}" method="POST">
        @csrf
        @include("admin.roles.partials.form")
        <div class="mb-5">
            <div class="d-flex flex-wrap gap-2 justify-content-end">
                <input type="submit" name="save" class="btn btn-success" value="@lang("translation.btn.save")">
                <a href="{{ route("admin.roles.index") }}" type="button"
                   class="btn btn-warning">@lang("translation.btn.back")</a>
            </div>
        </div>
    </form>
@endsection
