<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header border-0">
                <form action="{{ route('admin.exams.index') }}" method="get">
                    <div class="row g-4 align-items-center">
                        <div class="col-sm-3">
                            <div class="search-box">
                                <input name="search" type="text" class="form-control search" placeholder="@lang("translation.Search for...")" value="{{ $search }}">
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
                                    href="{{ route('admin.exams.create') }}">
                                    <i class="ri-add-line align-bottom me-1"></i>
                                    @lang("translation.exams.add")
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
                                    <th>@lang("translation.exams.name")</th>
                                    <th>@lang("translation.exams.tests_count")</th>
                                    <th>@lang("translation.exams.creator")</th>
                                    <th>@lang("translation.exams.created_at")</th>
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
                                        <a href="{{ route('admin.exams.show', $item->id) }}" class="text-reset">
                                            {{ $item->name }}
                                        </a>
                                    </td>
                                    <td>
                                        {{ $item->tests_count }}
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.users.show', $item->creator->id) }}"
                                            class="text-reset">
                                            {{ $item->creator?->name ?? '-' }}
                                        </a>
                                    </td>
                                    <td>
                                        {{ dateFormat($item->created_at) }}
                                    </td>
                                    <td class="text-nowrap">
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('admin.exams.show', $item->id) }}"
                                                class="btn border btn-ghost-dark waves-effect waves-light material-shadow-none btn-sm t-show-btn">
                                                <i class="mdi mdi-eye fs-5"></i>
                                            </a>
                                            <form action="{{ route('admin.exams.destroy', $item->id) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" onclick="return confirm('Are you sure?')"
                                                    class="btn border btn-ghost-danger waves-effect waves-light material-shadow-none btn-sm t-delete-btn">
                                                    <i class="mdi mdi-delete fs-5"></i>
                                                </button>
                                            </form>
                                        </div>
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

                <div class="offcanvas offcanvas-end" tabindex="-1" id="filterCanvas"
                    aria-labelledby="filterCanvasLabel">
                    <div class="offcanvas-header bg-light">
                        <h5 class="offcanvas-title" id="filterCanvas">@lang("translation.Filter")</h5>
                        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"
                            aria-label="Close"></button>
                    </div>
                    <!--end offcanvas-header-->
                    <form action="{{ route('admin.exams.index') }}"
                        class="d-flex flex-column justify-content-end h-100">
                        <div class="offcanvas-body">
                            <div class="mb-4">
                                <label for="datepicker-range"
                                    class="form-label text-muted text-uppercase fw-semibold mb-3">@lang('translation.Search')</label>
                                <input name="search" type="text" class="form-control search" placeholder="@lang("
                                    translation.Search for...")" value="{{ $search }}">
                            </div>
                        </div>
                        <!--end offcanvas-body-->
                        <div class="offcanvas-footer border-top p-3 text-center hstack gap-2">
                            <a class="btn btn-light w-100" href="{{ route('admin.exams.index') }}">
                                @lang("translation.Clear Filter")</a>
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
