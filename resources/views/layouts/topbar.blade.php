<header id="page-topbar">
    <div class="layout-width">
        <div class="navbar-header">
            <div class="d-flex align-items-center">
                <!-- LOGO -->
                <livewire:settings.logo-component />

                <button type="button" class="px-3 btn btn-sm fs-16 header-item vertical-menu-btn topnav-hamburger"
                    id="topnav-hamburger-icon">
                    <span class="hamburger-icon">
                        <span></span>
                        <span></span>
                        <span></span>
                    </span>
                </button>
            </div>

            <div class="d-flex align-items-center">
                <button type="button" class="btn text-nowrap d-flex align-items-center bg-primary-subtle text-primary"
                    data-bs-toggle="modal" data-bs-target="#branchModal">
                    <i class="ri-git-branch-line"></i>
                    <span class="text-primary">
                        {{ auth()->user()->currentBranch->name ?? 'Asosiy filial' }}
                    </span>
                </button>

                <div class="ms-1 header-item d-flex">
                    <button type="button" class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle search-btn"
                        data-bs-toggle="modal" data-bs-target="#searchModal"
                        onclick="document.getElementById('search').focus()">
                        <i class="mdi mdi-magnify fs-22"></i>
                    </button>
                </div>
                {{-- <div class="ms-1 header-item d-flex">
                    <button type="button" class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle search-btn"
                        data-bs-toggle="modal" data-bs-target="#searchModal"
                        onclick="document.getElementById('search').focus()">
                        <i class="mdi mdi-magnify fs-22"></i>
                    </button>
                </div>--}}
                {{-- <div class="dropdown ms-1 topbar-head-dropdown header-item">
                    <button type="button" class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle"
                        data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        @switch(Session::get('lang'))
                        @case('uz')
                        <img src="{{ asset('assets/images/flags/uz.svg') }}" class="rounded" alt="Header Language"
                            height="20">
                        @break
                        @case('ru')
                        <img src="{{ asset('assets/images/flags/russia.svg') }}" class="rounded" alt="Header Language"
                            height="20">
                        @break
                        @default
                        <img src="{{ asset('assets/images/flags/us.svg') }}" class="rounded" alt="Header Language"
                            height="20">
                        @endswitch
                    </button>
                    <div class="dropdown-menu dropdown-menu-end"> --}}
                        <!-- item-->
                        {{-- <a href="{{ url('index/en') }}" class="py-2 dropdown-item notify-item language"
                            data-lang="en" title="English">
                            <img src="{{ asset('assets/images/flags/us.svg') }}" alt="user-image" class="rounded me-2"
                                height="20">
                            <span class="align-middle">English</span>
                        </a> --}}

                        <!-- item-->
                        {{-- <a href="{{ route('admin.lang', 'ru') }}" class="dropdown-item notify-item language"
                            data-lang="ru" title="Russian">
                            <img src="{{ asset('assets/images/flags/russia.svg') }}" alt="user-image"
                                class="rounded me-2" height="20">
                            <span class="align-middle">Р СѓСЃСЃРєРёР№</span>
                        </a>

                        <!-- item-->
                        <a href="{{ route('admin.lang', 'uz') }}" class="dropdown-item notify-item language"
                            data-lang="uz" title="Uzbek">
                            <img src="{{asset('assets/images/flags/uz.svg')}}" alt="user-image" class="rounded me-2"
                                height="18">
                            <span class="align-middle">O'zbek</span>
                        </a>
                    </div>
                </div> --}}

                {{-- <div class="ms-1 header-item d-none d-sm-flex">
                    <button type="button" class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle"
                        data-toggle="fullscreen">
                        <i class='bx bx-fullscreen fs-22'></i>
                    </button>
                </div> --}}


                <div class="ms-1 header-item d-none d-sm-flex">
                    <a href="{{ route('admin.dark-mode') }}" type="button"
                        class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle light-dark-mode theme-btn"
                        id="themeSwitcher">
                        <i class='bx bx-moon fs-22'></i>
                    </a>
                </div>

                <div class="dropdown topbar-head-dropdown ms-1 header-item">
                    <button type="button" class="btn btn-icon btn-topbar rounded-circle quick-actions-dropdown"
                        data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="bx bx-category-alt fs-22"></i>
                    </button>
                    <div class="p-0 dropdown-menu dropdown-menu-lg dropdown-menu-end">
                        <div class="p-3 border border-dashed border-top-0 border-start-0 border-end-0">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h6 class="m-0 fw-semibold fs-15"> @lang("translation.quick_actions") </h6>
                                </div>
                            </div>
                        </div>

                        <div class="p-2">
                            <div class="row g-0">
                                @can('course-create')
                                <div class="col">
                                    <a class="dropdown-icon-item" href="{{ route('admin.courses.create') }}">
                                        <span class="mdi mdi-folder-multiple-plus-outline fs-3 text-success"></span>
                                        <span>@lang("translation.courses.add")</span>
                                    </a>
                                </div>
                                @endcan
                                @can('group-create')
                                <div class="col">
                                    <a class="dropdown-icon-item" href="{{ route('admin.groups.create') }}">
                                        <span class="mdi mdi-layers-plus fs-2 text-info"></span>
                                        <span>@lang("translation.groups.add")</span>
                                    </a>
                                </div>
                                @endcan
                            </div>

                            <div class="row g-0">
                                <div class="col">
                                    <a class="dropdown-icon-item" href="{{ route('admin.students.create') }}">
                                        <span class="mdi mdi-account-plus fs-2 text-primary"></span>
                                        <span>@lang("translation.students.add")</span>
                                    </a>
                                </div>
                                @can('expense-create')
                                <div class="col">
                                    <a class="dropdown-icon-item" href="{{ route('admin.expenses.create') }}">
                                        <span class="mdi mdi-cash-fast fs-2 text-danger"></span>
                                        <span>@lang("translation.expenses.add")</span>
                                    </a>
                                </div>
                                @endcan
                            </div>
                        </div>
                    </div>
                </div>

                <div class="ms-1 header-item d-flex align-items-center">
                    <livewire:reminders.combined-reminder-notifications />
                </div>

                <div class="dropdown ms-sm-3 header-item"
                    style="border-left: 0.5px dotted #878a99; border-right: 0.5px dotted #878a99">
                    <button type="button" class="btn" id="page-header-user-dropdown" data-bs-toggle="dropdown"
                        aria-haspopup="true" aria-expanded="false">
                        <span class="d-flex align-items-center">
                            <img class="rounded-circle header-profile-user"
                                src="@if(auth()->user()->getFirstMediaUrl('avatar')) {{auth()->user()->getFirstMediaUrl('avatar')}} @else {{ asset('images/markaz-lms.svg') }} @endif"
                                alt="Header Avatar" style="object-fit: cover;">
                            <span class="text-start ms-xl-2">
                                <span class="d-none d-xl-inline-block ms-1 fw-medium user-name-text">
                                    {{auth()->user()->name}}
                                </span>
                                <span class="d-none d-xl-block ms-1 fs-12 user-name-sub-text">
                                    {{auth()->user()->roles->first()?->name}}
                                </span>
                            </span>
                        </span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                        <!-- item-->
                        {{-- <h6 class="dropdown-header">@lang("translation.welcome") {{auth()->user()->name}}!</h6>--}}
                        <a class="dropdown-item" href="{{ route('admin.profile.settings') }}"><i
                                class="align-middle mdi mdi-account-circle text-muted fs-16 me-1"></i> <span
                                class="align-middle">@lang("translation.profile.profile")</span></a>
                        @can('settings-index')
                        <a class="dropdown-item" href="{{ route('admin.settings.index') }}">
                            <i class="align-middle mdi mdi-cog text-muted fs-16 me-1"></i>
                            <span class="align-middle">@lang("translation.settings")</span>
                        </a>
                        @endcan
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="javascript:void(0);"
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><i
                                class="align-middle bx bx-power-off font-size-16 me-1"></i> <span
                                key="t-logout">@lang('translation.logout')</span></a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- Modals -->
@include('layouts.partials.topbar-modals')
<livewire:search-modal />
