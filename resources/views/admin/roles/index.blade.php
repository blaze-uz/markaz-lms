@extends('layouts.master')
@section('title')
@lang('translation.roles.roles')
@endsection
@section('content')
{{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header border-0">
                <form action="{{ route("admin.roles.index") }}" method="get">
                    <div class="row g-4 align-items-center">
                        <div class="col-sm-3">
                            <div class="search-box">
                                <input name="search" type="text" class="form-control search" placeholder="@lang("translation.search")" value="{{ $filter->search }}">
                                <i class="ri-search-line search-icon"></i>
                            </div>
                        </div>
                        <div class="col-sm-auto ms-auto">
                            <div class="hstack gap-2">
                                <button id="deleteMultiple" type="button" class="btn btn-soft-danger" data-bs-toggle="modal" href="#deleteRecordModal">
                                    <i class="ri-delete-bin-2-line"></i>
                                </button>

                                <button type="button" class="btn btn-info" data-bs-toggle="offcanvas" href="#filterCanvas">
                                    <i class="ri-filter-3-line align-bottom me-1"></i> @lang("translation.Filter")
                                </button>

                                <a type="button" class="btn btn-success add-btn" href="{{ route('admin.roles.create') }}">
                                    <i class="ri-add-line align-bottom me-1"></i>
                                    @lang("translation.roles.add")
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
                                    <th scope="col" style="width: 50px;">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="checkAll" value="option">
                                        </div>
                                    </th>
                                    <th>ID</th>
                                    <th>@lang("translation.roles.name")</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody class="list form-check-all">
                                @foreach($items as $item)
                                <tr>
                                    <th scope="row">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="chk_child" value="{{ $item->id }}">
                                        </div>
                                    </th>
                                    <td style="width: 25px">
                                        {{ $item->id }}
                                    </td>
                                    <td>
                                        {{ $item->name }}
                                    </td>
                                    <td>
                                        <x-action-btn.buttons :showable="false" :editable="true" :edit-url="route('admin.roles.edit',['role' => $item->id])" :id="$item->id"></x-action-btn.buttons>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @else
                        <x-no-result />
                        @endif
                    </div>
                    <div class="d-flex justify-content-end mt-3">
                        {{ $items->links() }}
                    </div>
                </div>

                <!-- Delete modal -->
                <x-delete-modal :url="route('admin.roles.delete')"></x-delete-modal>
                <!--end modal -->

                <div class="offcanvas offcanvas-end" tabindex="-1" id="filterCanvas" aria-labelledby="filterCanvasLabel">
                    <div class="offcanvas-header bg-light">
                        <h5 class="offcanvas-title" id="filterCanvas">@lang("translation.Filter")</h5>
                        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                    </div>
                    <!--end offcanvas-header-->
                    <form action="{{ route("admin.roles.index") }}" class="d-flex flex-column justify-content-end h-100">
                        <div class="offcanvas-body">
                            <div class="mb-4">
                                <label for="datepicker-range" class="form-label text-muted text-uppercase fw-semibold mb-3">@lang('translation.Search')</label>
                                <input name="search" type="text" class="form-control search" placeholder="@lang(" translation.Search for...")" value="{{ $filter->search }}">
                            </div>
                        </div>
                        <!--end offcanvas-body-->
                        <div class="offcanvas-footer border-top p-3 text-center hstack gap-2">
                            <a class="btn btn-light w-100" href="{{ route("admin.roles.index") }}">@lang("translation.Clear Filter")</a>
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

<!-- <div class="row">
    <div class="col-lg-12">
    </div>
</div> -->
@endsection
@section('script')
<script src="{{ asset('assets/js/manage/delete-btn-component.js') }}"></script>
@endsection