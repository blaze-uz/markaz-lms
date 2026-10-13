@extends('layouts.master')
@section('title')
    @lang("translation.permissions.edit")
@stop
@section('content')
    {{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}
    <form action="{{ route('admin.permissions.update', $item->id) }}" method="POST">
        @csrf
        @method("PUT")
        <div class="card">
            @include("admin.permissions.partials.form")
        </div>
        <div class="hstack gap-2 justify-content-end">
            <input type="submit" name="save" class="btn btn-success" value="@lang("translation.btn.save")">
            <a href="{{ route("admin.permissions.index") }}" type="button"
               class="btn btn-warning">@lang("translation.btn.back")</a>
        </div>
    </form>
@endsection
