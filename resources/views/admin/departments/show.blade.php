@extends('layouts.master')
@section('title')
    @lang('translation.departments.department_details')
@endsection
@section('content')
    {{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">@lang('translation.departments.department_details')</h5>
                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.departments.edit', $item->id) }}" class="btn btn-success">
                                <i class="fas fa-edit"></i>
                                @lang('translation.btn.edit')
                            </a>
                            <a href="{{ route('admin.departments.index') }}" class="btn btn-warning">
                                <i class="fas fa-arrow-left"></i>
                                @lang('translation.btn.back')
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="200">@lang('translation.departments.name'):</th>
                                    <td>{{ $item->name }}</td>
                                </tr>
                                <tr>
                                    <th>@lang('translation.departments.description'):</th>
                                    <td>{{ $item->description ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>@lang('translation.departments.users'):</th>
                                    <td>
                                        @if ($item->users->count() > 0)
                                            @foreach ($item->users as $user)
                                                <span class="badge bg-info-subtle text-info me-1">{{ $user->name }}</span>
                                            @endforeach
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>@lang('translation.departments.status'):</th>
                                    <td>
                                        @if ($item->status == 'active')
                                            <span class="badge bg-success-subtle text-success">
                                                @lang('translation.departments.active')
                                            </span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger">
                                                @lang('translation.departments.inactive')
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>@lang('translation.Created at'):</th>
                                    <td>{{ $item->created_at->format('Y-m-d H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <th>@lang('translation.Updated at'):</th>
                                    <td>{{ $item->updated_at->format('Y-m-d H:i:s') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

