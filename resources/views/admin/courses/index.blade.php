@extends('layouts.master')
@section('title')
    @lang('translation.courses.courses')
@endsection
@section('content')
    {{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header border-0">
                    <form action="{{ route("admin.courses.index") }}" method="get">
                        <div class="row g-4 align-items-center">
                            <div class="col-sm-3">
                                <div class="search-box">
                                    <input name="search" type="text" class="form-control search"
                                           placeholder="@lang("translation.Search for...")"
                                           value="{{ $filter->search }}">
                                    <i class="ri-search-line search-icon"></i>
                                </div>
                            </div>
                            <div class="col-sm-auto ms-auto">
                                <div class="hstack gap-2">
                                    <button type="button" class="btn btn-info" data-bs-toggle="offcanvas"
                                            href="#filterCanvas">
                                        <i class="ri-filter-3-line align-bottom me-1"></i> @lang("translation.Filter")
                                    </button>

                                    <a type="button" class="btn btn-success add-btn"
                                       href="{{ route('admin.courses.create') }}">
                                        <i class="ri-add-line align-bottom me-1"></i>
                                        @lang("translation.courses.add")
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="card-body">
                    <div>
                        <div class="table-responsive table-card">
                            @if($items->count())
                                <table class="table align-middle" id="usersTable">
                                    <thead class="table-light align-middle">
                                    <tr>
                                        <th>ID</th>
                                        <th>@lang("translation.courses.name")</th>
                                        <th>@lang("translation.courses.duration")</th>
                                        <th>@lang("translation.courses.groups_count")</th>
                                        {{-- <th>@lang("translation.courses.price")</th> --}}
                                        <th></th>
                                    </tr>
                                    </thead>
                                    <tbody class="list form-check-all">
                                    @foreach($items as $item)
                                        <tr>
                                            <td>
                                                {{ $item->id }}
                                            </td>
                                            <td class="text-nowrap">
                                                <a href="{{ route('admin.courses.show', $item->id) }}"
                                                   class="text-reset">
                                                    {{ $item->name }}
                                                </a>
                                            </td>
                                            <td>
                                                {{ $item->duration }}
                                            </td>
                                            <td>
                                                <span class="badge bg-success-subtle text-success">
                                                    {{ $item->groups()->count() }}
                                                </span>
                                            </td>
                                            {{-- <td class="text-nowrap">
                                                {{ money($item->price) }}
                                            </td> --}}
                                            <td class="text-nowrap">
                                                <x-action-btn.buttons
                                                        :showable="true"
                                                        :editable="true"
                                                        :edit-url="route('admin.courses.edit',['course' => $item->id])"
                                                        :show-url="route('admin.courses.show',['course' => $item->id])"
                                                        :id="$item->id"
                                                        :deletable="false"
                                                ></x-action-btn.buttons>
                                                <form action="{{ route('admin.courses.destroy', $item) }}"
                                                      class="d-inline-block" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-sm"
                                                            data-bs-toggle="tooltip"
                                                            title="@lang('translation.btn.delete')"
                                                            onclick="return confirm('@lang('translation.Are you sure you want to delete?')')">
                                                        <i class="ri-delete-bin-line"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            @else
                                <x-no-result/>
                            @endif
                        </div>
                        <div class="d-flex justify-content-end mt-3">
                            {{ $items->links() }}
                        </div>
                    </div>

                    <div class="offcanvas offcanvas-end" tabindex="-1" id="filterCanvas"
                         aria-labelledby="filterCanvasLabel">
                        <div class="offcanvas-header bg-light">
                            <h5 class="offcanvas-title" id="filterCanvas">@lang("translation.Filter")</h5>
                            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"
                                    aria-label="Close"></button>
                        </div>
                        <!--end offcanvas-header-->
                        <form action="{{ route("admin.courses.index") }}"
                              class="d-flex flex-column justify-content-end h-100">
                            <div class="offcanvas-body">
                                <div class="mb-4">
                                    <label for="datepicker-range"
                                           class="form-label text-muted text-uppercase fw-semibold mb-3">@lang('translation.Search')</label>
                                    <input name="search" type="text" class="form-control search"
                                           placeholder="@lang("translation.Search for...")"
                                           value="{{ $filter->search }}">
                                </div>
                            </div>
                            <!--end offcanvas-body-->
                            <div class="offcanvas-footer border-top p-3 text-center hstack gap-2">
                                <a class="btn btn-light w-100"
                                   href="{{ route("admin.courses.index") }}">@lang("translation.Clear Filter")</a>
                                <button type="submit" class="btn btn-success w-100">@lang("translation.Filter")</button>
                            </div>
                            <!--end offcanvas-footer-->
                        </form>
                    </div>
                    <!--end offcanvas-->
                </div>
            </div>

        </div>
        <!--end col-->
    </div>
@endsection
