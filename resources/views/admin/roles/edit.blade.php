@extends('layouts.master')
@section('title')
@lang("translation.roles.edit")
@stop
@section('content')
{{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}
@if ($item->name == "Admin")
<div class="alert alert-warning">
    @lang("translation.admin_role_warning")!
</div>
<div class="d-flex justify-content-center align-items-center">
    <a href="{{ route('admin.roles.index') }}" class="btn btn-warning d-flex align-items-center">
        <i class="ri-arrow-left-line me-1"></i>
        @lang("translation.btn.back")
    </a>
</div>
@else
<form action="{{ route('admin.roles.update', $item->id) }}" method="POST">
    @csrf
    @method("PUT")
    <div>
        @include("admin.roles.partials.form")
    </div>
    <div class="mb-5">
        <div class="gap-2 hstack justify-content-end">
            <input type="submit" name="save" class="btn btn-success" value="@lang('translation.btn.save')">
            <a href="{{ route('admin.roles.index') }}" type="button"
                class="btn btn-warning">@lang("translation.btn.back")</a>
        </div>
    </div>
</form>
@endif
@endsection