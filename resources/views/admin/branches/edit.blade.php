@extends('layouts.master')
@section('title')
    @lang("translation.courses.edit")
@stop
@section('content')
    {{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}
    <form action="{{ route('admin.branches.update', $item->id) }}" method="POST">
        @csrf
        @method("PUT")
        <div class="card">
            @include("admin.branches.partials.form")
        </div>
        <div class="mb-5">
            <div class="hstack gap-2 justify-content-end">
                <input type="submit" name="save" class="btn btn-success" value="@lang("translation.btn.save")">
                
                <a href="{{ route("admin.branches.index") }}" type="button"
                   class="btn btn-warning">@lang("translation.btn.back")</a>
            </div>
        </div>
    </form>
@endsection
