@extends('layouts.master')

@section("title")
    {{ $item->name }}
@stop

@section('content')
    {{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}

    <div class="row">
        <div class="col-md-4 col-sm-12">
            <div class="card">
                <div class="card-body p-4">
                    <div>
                        <div class="mt-4 text-center">
                            <h5 class="mb-1">{{ $item->name }}</h5>
                            <p class="text-muted">@lang("attr.create_date") {{ dateFormat($item->created_at) }}</p>
                        </div>
                        <div class="table-responsive">
                            <table class="table mb-0 table-borderless">
                                <tbody>
                                    {{-- <tr>
                                        <th>
                                            <span class="fw-medium">
                                                @lang("translation.courses.price")
                                            </span>
                                        </th>
                                        <td>{{ money($item->price) }}</td>
                                    </tr> --}}
                                <tr>
                                    <th>
                                        <span class="fw-medium">
                                            @lang("translation.courses.duration")
                                        </span>
                                    </th>
                                    <td>{{ $item->duration }}</td>
                                </tr>
                                <tr>
                                    <th>
                                        <span class="fw-medium">
                                            @lang("translation.courses.description")
                                        </span>
                                    </th>
                                    <td>{{ $item->description }}</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-between mt-3">
                            <a class="btn btn-warning" href="{{ route('admin.courses.index') }}">
                                <i class="mdi mdi-arrow-left"></i>
                                @lang("translation.btn.back")
                            </a>
                            <a class="btn btn-info"
                               href="{{ route('admin.courses.edit', $item->id) }}">
                                <i class="mdi mdi-pencil"></i>
                                @lang("translation.btn.edit")
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
