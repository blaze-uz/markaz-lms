@extends('layouts.master')
@section('title')
    @lang('translation.departments.departments')
@endsection
@section('content')
    {{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}
    <div class="card">
        <div class="card-header">
            <div class="row g-4 align-items-center">
                <div class="col-sm-3">
                    <form action="{{ route('admin.departments.index') }}" method="get">
                        <div class="search-box">
                            <input name="search" type="text" class="form-control search"
                                placeholder="@lang('translation.Search for...')" value="{{ $filter->search ?? '' }}">
                            <i class="ri-search-line search-icon"></i>
                        </div>
                    </form>
                </div>
                <div class="col-sm-auto ms-auto">
                    <div class="hstack gap-2">
                        <button type="button" class="btn btn-info" data-bs-toggle="offcanvas" href="#filterCanvas">
                            <i class="ri-filter-3-line align-bottom me-1"></i> @lang('translation.Filter')
                        </button>
                        <a type="button" class="btn btn-success add-btn" href="{{ route('admin.departments.create') }}">
                            <i class="ri-add-line align-bottom me-1"></i>
                            @lang('translation.departments.add')
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive table-card">
                @if ($items->count())
                    <table class="table align-middle" id="departmentsTable">
                        <thead class="table-light align-middle">
                            <tr>
                                <th>ID</th>
                                <th>@lang('translation.departments.name')</th>
                                <th>@lang('translation.departments.description')</th>
                                <th>@lang('translation.departments.status')</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody class="list form-check-all">
                            @foreach ($items as $item)
                                <tr>
                                    <td>{{ $item->id }}</td>
                                    <td class="text-nowrap">
                                        <a href="{{ route('admin.departments.show', $item->id) }}" class="text-reset">
                                            {{ $item->name }}
                                        </a>
                                    </td>
                                    <td>{{ Str::limit($item->description ?? '-', 50) }}</td>
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
                                    <td class="text-nowrap">
                                        <x-action-btn.buttons :showable="true" :editable="true"
                                            :edit-url="route('admin.departments.edit', ['department' => $item->id])"
                                            :show-url="route('admin.departments.show', ['department' => $item->id])"
                                            :deletable="true"
                                            :id="$item->id"
                                            :delete-modal-id="'deleteRecordModal'" />
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <x-no-result />
                @endif
            </div>
            <div class="d-flex justify-content-end">
                {{ $items->links() }}
            </div>
        </div>
    </div>

    <!-- Filter Offcanvas -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="filterCanvas" aria-labelledby="filterCanvasLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="filterCanvasLabel">@lang('translation.Filter')</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <form action="{{ route('admin.departments.index') }}" method="get">
                <div class="mb-3">
                    <label for="status" class="form-label">@lang('translation.departments.status')</label>
                    <select name="status" id="status" class="form-control">
                        <option value="">@lang('translation.All')</option>
                        <option value="active" {{ ($filter->status ?? '') == 'active' ? 'selected' : '' }}>
                            @lang('translation.departments.active')
                        </option>
                        <option value="inactive" {{ ($filter->status ?? '') == 'inactive' ? 'selected' : '' }}>
                            @lang('translation.departments.inactive')
                        </option>
                    </select>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">@lang('translation.Apply')</button>
                    <a href="{{ route('admin.departments.index') }}" class="btn btn-secondary">
                        @lang('translation.Reset')
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete modal -->
    <x-delete-modal :url="route('admin.departments.destroy', ['department' => 0])" modal-id="deleteRecordModal"></x-delete-modal>
    <!--end modal -->
@endsection

@section('script')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const deletingItemsBox = document.getElementById('deletingItemsBox');
        
        if (deletingItemsBox) {
            // Clear delete box function
            function clearDeleteBox() {
                deletingItemsBox.innerHTML = '';
            }
            
            // Push item to delete box
            function pushItemToDeleteBox(id) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'ids[]';
                input.value = id;
                deletingItemsBox.appendChild(input);
            }
            
            // Handle delete button clicks
            document.querySelectorAll('.destroy').forEach(function(item) {
                item.addEventListener('click', function() {
                    const itemId = this.getAttribute('data-item-id');
                    clearDeleteBox();
                    if (itemId) {
                        pushItemToDeleteBox(itemId);
                        // Update form action
                        const form = document.getElementById('deleteItemsForm');
                        if (form) {
                            form.action = "{{ route('admin.departments.destroy', ['department' => ':id']) }}".replace(':id', itemId);
                        }
                    }
                });
            });
        }
    });
</script>
@endsection

