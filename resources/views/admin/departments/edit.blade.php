@extends('layouts.master')
@section('title')
    @lang('translation.departments.edit')
@endsection
@section('content')
    {{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}
    <form action="{{ route('admin.departments.update', $item->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 col-sm-12">
                        <div class="form-group mb-3">
                            <label for="name">
                                @lang('translation.departments.name') <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
                                placeholder="@lang('translation.departments.name')"
                                value="{{ old('name', $item->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6 col-sm-12">
                        <div class="form-group mb-3">
                            <label for="status" class="form-label d-block mb-2">
                                @lang('translation.departments.status')
                            </label>
                            <div class="d-flex gap-2">
                                <input type="radio" class="btn-check" name="status" id="status_active" value="active"
                                    {{ old('status', $item->status ?? 'active') == 'active' ? 'checked' : '' }}>
                                <label class="btn btn-outline-success" for="status_active">
                                    @lang('translation.departments.active')
                                </label>

                                <input type="radio" class="btn-check" name="status" id="status_inactive" value="inactive"
                                    {{ old('status', $item->status) == 'inactive' ? 'checked' : '' }}>
                                <label class="btn btn-outline-danger" for="status_inactive">
                                    @lang('translation.departments.inactive')
                                </label>
                            </div>
                            @error('status')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-12 col-sm-12">
                        <div class="form-group mb-3">
                            <label for="description">
                                @lang('translation.departments.description')
                            </label>
                            <textarea name="description" id="description" rows="4"
                                class="form-control @error('description') is-invalid @enderror"
                                placeholder="@lang('translation.departments.description')">{{ old('description', $item->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-12 col-sm-12">
                        <div class="form-group mb-3">
                            <label for="users">
                                @lang('translation.departments.users')
                            </label>
                            <select name="users[]" id="users" class="form-control" multiple>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}"
                                        {{ (old('users') && in_array($user->id, old('users'))) || (isset($item) && $item->users->contains($user->id)) ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('users')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            @error('users.*')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="mb-5">
            <div class="d-flex flex-wrap gap-2 justify-content-end">
                <x-button id="detach-btn" class="btn-success">
                    @lang('translation.btn.save')
                </x-button>
                <a href="{{ route('admin.departments.index') }}" type="button" class="btn btn-warning">
                    @lang('translation.btn.back')
                </a>
            </div>
        </div>
    </form>
@endsection

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endsection

@section('script-bottom')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#users').select2({
                placeholder: "@lang('translation.departments.choose_users')",
                allowClear: true,
                multiple: true,
                closeOnSelect: false,
            });
        });
    </script>
@endsection

