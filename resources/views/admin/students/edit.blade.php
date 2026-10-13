@extends('layouts.master')
@section('title')
@lang("translation.students.edit")
@stop
@section('content')
{{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}
<form action="{{ route('admin.students.update', $item->id) }}" method="POST" enctype="multipart/form-data" autocomplete="off">
    @csrf
    @method("PUT")
    <div class="card">
        @include("admin.students.partials.form")
    </div>
    <div class="card">
        <div class="card-header">
            @lang("translation.groups.groups")
        </div>
        <div class="card-body">
            @error('attached_at.*')
            <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            @forelse($groups as $group)
            <div class="mb-3">
                <label for="{{$group->id}}">
                    {{ $group->name }}
                </label>
                <input id="{{$group->id}}" type="date" class="form-control" name="attached_at[{{$group->id}}]" value="{{ dateFormat($group->pivot->attached_at, 'Y-m-d') }}">
            </div>
            @empty
            <x-no-result />
            @endforelse
        </div>
    </div>
    <div class="mb-5">
        <div class="hstack gap-2 justify-content-end">
            <x-button id="detach-btn" class="btn-success">
                @lang("translation.btn.save")
            </x-button>
            <a href="{{ route("admin.students.index") }}" type="button" class="btn btn-warning">@lang("translation.btn.back")</a>
        </div>
    </div>
</form>
@endsection
