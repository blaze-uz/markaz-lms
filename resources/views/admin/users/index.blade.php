@extends('layouts.master')
@section('title')
@lang('translation.users.users')
@endsection
@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.css"
    integrity="sha512-H9jrZiiopUdsLpg94A333EfumgUBpO9MdbxStdeITo+KEIMaNfHNvwyjjDJb+ERPaRS6DpyRlKbvPUasNItRyw=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />
@endsection
@section('content')
{{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}
<div class="row">
    <div class="col-lg-12">
        <div class="card mb-3">
            <div class="card-body">
                <div
                    class="d-flex flex-column flex-xl-row flex-xl-nowrap align-items-stretch align-items-xl-end gap-3 w-100 overflow-x-auto">
                    <form action="{{ route('admin.users.index') }}" method="get"
                        class="d-flex flex-column flex-lg-row flex-lg-nowrap align-items-stretch align-items-lg-end gap-2 gap-lg-3 flex-grow-1 min-w-0 m-0">
                        <div
                            class="d-none d-lg-flex align-items-center flex-shrink-0 text-muted fw-semibold small text-uppercase pb-2 lh-1">
                        </div>
                        <div class="flex-grow-1 flex-shrink-1 min-w-0" style="flex-basis: 7rem;">
                            <label for="users-filter-search"
                                class="form-label text-muted text-uppercase fw-semibold small mb-2">@lang('translation.Search')</label>
                            <div class="search-box">
                                <input id="users-filter-search" name="search" type="text" class="form-control search"
                                    placeholder="@lang('translation.Search for...')" value="{{ $filter->search }}">
                                <i class="ri-search-line search-icon"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 flex-shrink-1 min-w-0" style="flex-basis: 7rem;">
                            <label for="roles"
                                class="form-label text-muted text-uppercase fw-semibold small mb-2">@lang('translation.roles.roles')</label>
                            <select name="role" id="roles" class="form-control form-select">
                                <option value="">@lang('translation.select')</option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->id }}" @selected($role->id == $filter->role)>
                                        {{ $role->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex-grow-1 flex-shrink-1 min-w-0" style="flex-basis: 7rem;">
                            <label for="department_id"
                                class="form-label text-muted text-uppercase fw-semibold small mb-2">@lang('translation.departments.departments')</label>
                            <select name="department_id" id="department_id" class="form-control form-select">
                                <option value="">@lang('translation.select')</option>
                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }}" @selected($department->id == $filter->department_id)>
                                        {{ $department->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex-grow-1 flex-shrink-1 min-w-0" style="flex-basis: 5rem;">
                            <label for="per_page"
                                class="form-label text-muted text-uppercase fw-semibold small mb-2">@lang('translation.per_page')</label>
                            <select name="per_page" id="per_page" class="form-control form-select">
                                <option value="20" @selected(request('per_page', 20) == 20)>20</option>
                                <option value="50" @selected(request('per_page') == 50)>50</option>
                                <option value="100" @selected(request('per_page') == 100)>100</option>
                                <option value="250" @selected(request('per_page') == 250)>250</option>
                                <option value="500" @selected(request('per_page') == 500)>500</option>
                            </select>
                        </div>
                        <div class="d-flex flex-wrap gap-2 flex-shrink-0 align-items-end">
                            <a class="btn btn-light" href="{{ route('admin.users.index') }}">
                                @lang('translation.Clear Filter')
                            </a>
                            <button type="submit" class="btn btn-success">
                                @lang('translation.Filter')
                            </button>
                        </div>
                    </form>
                    <div
                        class="d-flex flex-wrap gap-2 align-items-center flex-shrink-0 justify-content-start justify-content-xl-end ms-xl-auto">
                        <form id="sendSmsForm" action="{{ route('admin.sms.create') }}" class="d-none mb-0"
                            style="display: inline;">
                            <input type="hidden" name="sendSmsIds" id="selectedIds">
                            <input type="hidden" name="sendSmsType" value="staff">
                            <button type="submit" id="send-sms-button" data-bs-toggle="tooltip"
                                title="@lang('translation.users.send_sms')"
                                class="btn btn-warning nav-link btn-icon fs-14 active align-middle">
                                <i class="ri-message-2-fill"></i>
                            </button>
                        </form>
                        <button type="button" id="send-telegram-button" data-bs-toggle="modal"
                            data-bs-target="#sendTelegramModalMultiple"
                            class="btn btn-info nav-link btn-icon fs-14 active d-none align-middle justify-content-center d-flex align-items-center"
                            title="@lang('translation.users.send_telegram')" style="display: inline;">
                            <i class="ri-telegram-fill d-flex align-items-center justify-content-center"></i>
                        </button>
                        <button type="button" id="grid-view-button"
                            class="btn btn-soft-info nav-link btn-icon fs-14 active filter-button">
                            <i class="ri-grid-fill"></i>
                        </button>
                        <button type="button" id="list-view-button"
                            class="btn btn-soft-info nav-link btn-icon fs-14 filter-button">
                            <i class="ri-list-unordered"></i>
                        </button>
                        <a type="button" class="btn btn-success add-btn" href="{{ route('admin.users.create') }}">
                            <i class="align-bottom ri-add-line me-1"></i>
                            @lang("translation.users.add")
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div id="team-card">
                    <div class="team-list grid-view-filter row" id="team-member-list">
                        @if($items->count())
                        @foreach($items as $item)
                        <div class="col">
                            <div class="card team-box">
                                <div class="team-cover">

                                </div>
                                <div class="p-4 card-body">
                                    <div class="row align-items-center team-row">
                                        <div class="col team-settings">
                                            <div class="d-flex justify-content-end">
                                                <div class="text-end dropdown">
                                                    <a href="javascript:void(0);" data-bs-toggle="dropdown"
                                                        aria-expanded="false">
                                                        <i class="ri-more-fill fs-17"></i>
                                                    </a>
                                                    <ul class="dropdown-menu dropdown-menu-end">
                                                        <li>
                                                            <a class="dropdown-item edit-list"
                                                                href="{{ route('admin.users.payment.view', $item->id) }}">
                                                                <i
                                                                    class="align-bottom ri-money-dollar-circle-line me-2 text-muted"></i>
                                                                {{ __('translation.users.payment') }}
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item edit-list"
                                                                href="{{ route('admin.users.edit', $item->id) }}">
                                                                <i
                                                                    class="align-bottom ri-settings-line me-2 text-muted"></i>
                                                                {{ __('translation.users.settings') }}
                                                            </a>
                                                        </li>
                                                        @can('user-delete')
                                                        <li>
                                                            <form action="{{ route('admin.users.delete') }}"
                                                                method="POST">
                                                                @csrf
                                                                <input type="hidden" name="ids[]"
                                                                    value="{{ $item->id }}">
                                                                <button type="submit" class="dropdown-item"
                                                                    onclick="return confirm('Are you sure?')">
                                                                    <i
                                                                        class="align-bottom ri-delete-bin-2-line me-2 text-muted"></i>
                                                                    {{ __('translation.users.delete') }}
                                                                </button>
                                                            </form>
                                                        </li>
                                                        @endcan
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col">
                                            <div class="team-profile-img">
                                                @if($item->getFirstMedia('avatar'))
                                                <a href="{{ $item->getFirstMediaUrl('avatar') }}"
                                                    class="instruction-image">
                                                    <div style="
                                                        height: 75px;
                                                        border-radius: 50%;
                                                        width: 75px !important;
                                                        background-image: url('{{ $item->getFirstMediaUrl('avatar') }}') !important; background-size: cover;
                                                        background-position: center; object-fit: contain">
                                                    </div>
                                                </a>
                                                @else
                                                {!!
                                                Avatar::create($item->name)->setTheme('grayscale-dark')->setDimension(75)->setFontSize(30)->toSvg()
                                                !!}
                                                @endif
                                                <div class="team-content">
                                                    <a class="member-name" data-bs-toggle="offcanvas"
                                                        href="{{ route('admin.users.show', $item->id) }}"
                                                        aria-controls="member-overview">
                                                        <h5 class="mb-1 fs-16">{{ $item->name }}</h5>
                                                    </a>
                                                    <p class="mb-0 text-muted member-designation">
                                                        {{ $item->email }}
                                                    </p>
                                                    <p class="mb-0 text-muted member-designation">
                                                        {{ formattedPhone($item->phone) }}
                                                        | {{ dateFormat($item->birthday) }}
                                                    </p>
                                                    <p class="mb-0 text-muted member-designation">
                                                        <i class="ri-star-fill text-warning"></i> {{ $item->stars ?? 0 }} {{ __('translation.users.stars') }}
                                                    </p>
                                                    <div>
                                                        @foreach($item->roles as $role)
                                                        <span class="badge bg-success-subtle text-success">
                                                            {{ $role->name }}
                                                        </span>
                                                        @endforeach
                                                        @if($item->percentage == 0 && !$item->hasRole('Admin'))
                                                        <span
                                                            class="cursor-pointer badge bg-warning-subtle text-warning"
                                                            data-bs-toggle="tooltip"
                                                            title="{{__('translation.users.not_agreed')}}">
                                                            <i class="fas fa-exclamation-triangle"></i>
                                                        </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-2 col">
                                            <div class="text-end">
                                                <a href="{{ route('admin.users.show', $item->id) }}"
                                                    class="btn btn-light view-btn">
                                                    {{ __('translation.users.view') }}
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                        <div class="mt-3 d-flex justify-content-end">
                            {{ $items->links() }}
                        </div>
                        @else
                        <x-no-result />
                        @endif
                    </div>
                </div>
                <div id="team-list" class="d-none">
                    <div class="team-list row list-view-filter" id="team-member-list">
                        <div class="col">
                            <div class="mb-3 form-check">
                                <input class="form-check-input" type="checkbox" id="checkAll">
                                <label class="form-check-label" for="checkAll">
                                    {{ __('translation.users.select_all') }}
                                </label>
                            </div>
                        </div>
                        @foreach($items as $item)
                        <div class="col">
                            <div class="card team-box">
                                <div class="p-4 card-body">
                                    <div class="row align-items-center team-row">
                                        <div class="col team-settings">
                                            <div class="row">
                                                <div class="col text-end dropdown">
                                                    <a href="javascript:void(0);" data-bs-toggle="dropdown"
                                                        aria-expanded="false">
                                                        <i class="ri-more-fill fs-17"></i>
                                                    </a>
                                                    <ul class="dropdown-menu dropdown-menu-end">
                                                        <li>
                                                            <a class="dropdown-item edit-list"
                                                                href="{{ route('admin.users.payment.view', $item->id) }}">
                                                                <i
                                                                    class="align-bottom ri-money-dollar-circle-line me-2 text-muted"></i>
                                                                {{ __('translation.users.payment') }}
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item edit-list"
                                                                href="{{ route('admin.users.edit', $item->id) }}">
                                                                <i
                                                                    class="align-bottom ri-settings-line me-2 text-muted"></i>
                                                                {{ __('translation.users.settings') }}
                                                            </a>
                                                        </li>
                                                        @can('user-delete')
                                                        <li>
                                                            <form action="{{ route('admin.users.delete') }}"
                                                                method="POST">
                                                                @csrf
                                                                <input type="hidden" name="ids[]"
                                                                    value="{{ $item->id }}">
                                                                <button type="submit" class="dropdown-item"
                                                                    onclick="return confirm('Are you sure?')">
                                                                    <i
                                                                        class="align-bottom ri-delete-bin-2-line me-2 text-muted"></i>
                                                                    {{ __('translation.users.delete') }}
                                                                </button>
                                                            </form>
                                                        </li>
                                                        @endcan
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="row align-items-center">
                                                <div class="col-md-1 d-none d-md-block">
                                                    <input type="checkbox" class="form-check-input user-check-input"
                                                        id="selectCheckbox-{{$item->id}}" value="{{$item->id}}">
                                                </div>
                                                <div class="col-md-11 col-sm-12">
                                                    <div class="cursor-pointer team-profile-img">
                                                        <div class="avatar-lg img-thumbnail rounded-circle">
                                                            <div
                                                                class="border avatar-title bg-light text-primary rounded-circle text-uppercase">
                                                                @if($item->getFirstMedia('avatar'))
                                                                <a href="{{ $item->getFirstMediaUrl(" avatar") }}"
                                                                    class="instruction-image">
                                                                    <div style="
                                                                                    height: 65px;
                                                                                    border-radius: 50%;
                                                                                    width: 65px !important;
                                                                                    background-image: url('{{ $item->getFirstMediaUrl("
                                                                        avatar") }}') !important; background-size:
                                                                        cover; background-position: center; object-fit:
                                                                        contain">
                                                                    </div>
                                                                </a>
                                                                @else
                                                                {!!
                                                                Avatar::create($item->name)->setTheme('grayscale-dark')->setDimension(75)->setFontSize(30)->toSvg()
                                                                !!}
                                                                @endif
                                                            </div>
                                                        </div>
                                                        <div class="team-content">
                                                            <a class="member-name"
                                                                href="{{ route('admin.users.show', $item->id) }}">
                                                                <h5 class="mb-1 fs-16">
                                                                    {{ $item->name }}
                                                                </h5>
                                                            </a>
                                                            <p class="mb-0 text-muted member-designation">
                                                                {{ $item->email }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="text-center row text-muted">
                                                <div class="col-4 border-end border-end-dashed">
                                                    <h5 class="mb-1 groups-num">
                                                        {{ $item->groups->count() }}
                                                    </h5>
                                                    <p class="mb-0 text-muted">
                                                        {{__('translation.users.groups_count')}}
                                                    </p>
                                                </div>
                                                <div class="col-4 border-end border-end-dashed">
                                                    <h5 class="mb-1 students-num">
                                                        {{ $item->groups->flatMap->students->count() }}
                                                    </h5>
                                                    <p class="mb-0 text-muted">
                                                        {{__('translation.users.students_count')}}
                                                    </p>
                                                </div>
                                                <div class="col-4">
                                                    <h5 class="mb-1">
                                                        <i class="ri-star-fill text-warning"></i> {{ $item->stars ?? 0 }} {{ __('translation.users.stars') }}
                                                    </h5>
                                                    <p class="mb-0 text-muted">
                                                        {{ __('translation.users.stars_count') }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="text-end">
                                                <a href="{{ route('admin.users.show', $item->id) }}"
                                                    class="btn btn-light view-btn">
                                                    {{ __('translation.users.view') }}
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <div class="mt-3 d-flex justify-content-end">
                        {{ $items->links() }}
                    </div>
                </div>
            </div><!-- end col -->
        </div>
    </div>
    <!--end col-->
</div>

@endsection

<!-- Send Telegram Message Modal -->
<div class="modal fade" id="sendTelegramModalMultiple" tabindex="-1" aria-labelledby="sendTelegramModalMultipleLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="sendTelegramModalMultipleLabel">
                    @lang('translation.users.send_telegram')
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.users.send-telegram-to-multiple') }}" method="POST"
                id="sendTelegramFormMultiple">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="telegram_message_multiple" class="form-label">@lang('translation.telegram.message')</label>
                        <textarea class="form-control" id="telegram_message_multiple" name="message" rows="4" required></textarea>
                    </div>
                    <div id="selectedUsersList" class="mb-3">
                        <small class="text-muted">@lang('translation.users.selected_users_count'): <span id="selectedCount">0</span></small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                        @lang('translation.Close')
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="align-middle ri-send-plane-line me-1"></i>
                        @lang('translation.telegram.send')
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.js"
    integrity="sha512-uURl+ZXMBrF4AwGaWmEetzrd+J5/8NRkWAvJx5sbPSSuOb0bZLqf+tOzniObO00BjHa/dD7gub9oCGMLPQHtQA=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
    $(document).ready(function () {
            $(".instruction-image").fancybox({
                'transitionIn': 'elastic',
                'transitionOut': 'elastic',
                'speedIn': 600,
                'speedOut': 200,
                'overlayShow': false
            });

            // Check and set view from local storage
            let view = localStorage.getItem('view');

            // Set default view to grid
            if (view === null) {
                view = 'grid';
            }

            if (view === 'list') {
                toggleView('list');
            }

            // Grid view and list view click handlers
            $("#grid-view-button").click(function () {
                toggleView('grid');
                // Prevent page refresh
                return false;
            });

            $("#list-view-button").click(function () {
                toggleView('list');
                // Prevent page refresh
                return false;
            });

            function toggleView(view) {
                if (view === 'list') {
                    $("#team-card").removeClass("d-block").addClass("d-none");
                    $("#team-list").removeClass("d-none").addClass("d-block");
                    $("#grid-view-button").removeClass("active");
                    $("#list-view-button").addClass("active");
                } else {
                    $("#team-card").removeClass("d-none").addClass("d-block");
                    $("#team-list").removeClass("d-block").addClass("d-none");
                    $("#grid-view-button").addClass("active");
                    $("#list-view-button").removeClass("active");
                }
                localStorage.setItem('view', view);
            }

            // Initialize an empty array to store selected ids
            let selectedIds = [];

            // Listen for changes on checkboxes with the class ".user-check-input"
            $(".user-check-input").change(function () {
                // Get the id of the checkbox that triggered the change event
                let id = $(this).val();

                // Check if the checkbox is checked
                if ($(this).is(":checked")) {
                    // Add the id to the selectedIds array
                    selectedIds.push(id);
                } else {
                    // Remove the id from the selectedIds array
                    selectedIds = selectedIds.filter(function (value) {
                        return value != id;
                    });
                }

                // Set the value of the hidden input field to the selectedIds array
                $("#selectedIds").val(selectedIds);

                // Check if there are any selected ids
                if (selectedIds.length > 0) {
                    // If there are selected ids, show the buttons
                    $("#sendSmsForm").removeClass("d-none").addClass("d-inline-flex");
                    $("#send-telegram-button").removeClass("d-none");
                } else {
                    // If there are no selected ids, hide the buttons
                    $("#sendSmsForm").removeClass("d-inline-flex").addClass("d-none");
                    $("#send-telegram-button").addClass("d-none");
                }
            });

            // Check All functionality
            $('#checkAll').change(function () {
                let isChecked = $(this).is(':checked');

                $('.user-check-input').each(function () {
                    $(this).prop('checked', isChecked).trigger('change');
                });
            });

            // Update selected count in Telegram modal
            $('#sendTelegramModalMultiple').on('show.bs.modal', function (e) {
                $('#selectedCount').text(selectedIds.length);
                // Update hidden inputs with selected IDs
                $('#sendTelegramFormMultiple').find('input[name="user_ids[]"]').remove();
                selectedIds.forEach(function(id) {
                    $('#sendTelegramFormMultiple').append('<input type="hidden" name="user_ids[]" value="' + id + '">');
                });
            });
        });
</script>
@endsection