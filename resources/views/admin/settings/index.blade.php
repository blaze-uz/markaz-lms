@extends('layouts.master')
@section('title')
    @lang('translation.settings')
@endsection
@section('css')
    <style>
        .img-thumbnail {
            border: 3px solid #0db39c !important;
        }
    </style>
@endsection
@section('content')
    {{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}
    <div class="mb-3 row">
        <div class="col-md-3 col-sm-12"></div>
        <div class="col-md-6 col-sm-12">
            <a href="{{ route('admin.profile.settings') }}">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 col-5">
                                <div class="profile-user d-flex justify-content-center align-items-center">
                                    <img src="@if (auth()->user()->getFirstMediaUrl('avatar')) {{ auth()->user()->getFirstMediaUrl('avatar') }}
                                    @else {{ asset('images/markaz-lms.svg') }} @endif"
                                        class="rounded-circle avatar-lg img-thumbnail user-profile-image material-shadow"
                                        alt="user-profile-image">
                                </div>
                            </div>
                            <div class="col-md-6 col-5">
                                <div class="d-flex justify-content-start align-items-center h-100">
                                    <div>
                                        <h5 class="mb-1 fs-16">{{ auth()->user()->name }}</h5>
                                        <p class="mb-0 text-muted">{{ auth()->user()->roles()->first()->name }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-2">
                                <div class="d-flex justify-content-center align-items-center h-100">
                                    <div class="text-white rounded bg-success d-flex justify-content-center align-items-center"
                                        style="width: 35px; height: 35px">
                                        <i class="ri-arrow-right-s-line fw-medium fs-2"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
            <div class="mb-4">
                <input type="text" id="settingsSearch" class="form-control" placeholder="@lang('translation.search')..."
                    aria-label="Search settings">
            </div>
            <ul class="list-group">
                <li class="list-group-item">
                    <a href="{{ route('admin.settings.general-settings-index') }}" class="text-reset">
                        <div class="gap-2 d-flex align-items-center">
                            <div class="text-white rounded d-flex justify-content-center align-items-center bg-success"
                                style="width: 35px; height: 35px;">
                                <i class="ri-settings-3-fill fs-5"></i>
                            </div>
                            <div>
                                <span class="fs-5">
                                    @lang('translation.general-settings')
                                </span>
                            </div>
                        </div>
                    </a>
                </li>
                <li class="list-group-item">
                    <a href="{{ route('admin.settings.payment-types') }}" class="text-reset">
                        <div class="gap-2 d-flex align-items-center">
                            <div class="text-white rounded bg-success d-flex justify-content-center align-items-center"
                                style="width: 35px; height: 35px">
                                <i class="ri-currency-line fs-5"></i>
                            </div>
                            <div>
                                <span class="fs-5">
                                    @lang('translation.payment-type-settings')
                                </span>
                            </div>
                        </div>
                    </a>
                </li>
                <li class="list-group-item">
                    <a href="{{ route('admin.settings.menu-settings-view') }}" class="text-reset">
                        <div class="gap-2 d-flex align-items-center">
                            <div class="text-white rounded bg-success d-flex justify-content-center align-items-center"
                                style="width: 35px; height: 35px">
                                <i class="ri-menu-unfold-line fs-5"></i>
                            </div>
                            <div>
                                <span class="fs-5">
                                    @lang('translation.menu_settings')
                                </span>
                            </div>
                        </div>
                    </a>
                </li>
                <li class="list-group-item">
                    <a href="{{ route('admin.rooms.index') }}" class="text-reset">
                        <div class="gap-2 d-flex align-items-center">
                            <div class="text-white rounded bg-success d-flex justify-content-center align-items-center"
                                style="width: 35px; height: 35px">
                                <i class="ri-door-open-fill fs-5"></i>
                            </div>
                            <div>
                                <span class="fs-5">
                                    @lang('translation.rooms-settings')
                                </span>
                            </div>
                        </div>
                    </a>
                </li>
                <li class="list-group-item">
                    <a href="{{ route('admin.branches.index') }}" class="text-reset">
                        <div class="gap-2 d-flex align-items-center">
                            <div class="text-white rounded bg-success d-flex justify-content-center align-items-center"
                                style="width: 35px; height: 35px">
                                <i class="ri-git-branch-fill fs-5"></i>
                            </div>
                            <div>
                                <span class="fs-5">
                                    @lang('translation.branches.branch')
                                </span>
                            </div>
                        </div>
                    </a>
                </li>
                <li class="list-group-item">
                    <a href="{{ route('admin.settings.staff-settings-view') }}" class="text-reset">
                        <div class="gap-2 d-flex align-items-center">
                            <div class="gap-2 d-flex">
                                <div class="text-white rounded bg-success d-flex justify-content-center align-items-center"
                                    style="width: 35px; height: 35px">
                                    <i class="ri-user-3-fill fs-5"></i>
                                </div>
                                <div class="d-flex align-items-center">
                                    <span class="fs-5">
                                        @lang('translation.staff_settings')
                                    </span>
                                </div>
                            </div>
                        </div>
                    </a>
                </li>
                <li class="list-group-item">
                    <a href="{{ route('admin.settings.group-settings') }}" class="text-reset">
                        <div class="gap-2 d-flex align-items-center">
                            <div class="gap-2 d-flex">
                                <div class="text-white rounded bg-success d-flex justify-content-center align-items-center"
                                    style="width: 35px; height: 35px">
                                    <i class="ri-group-line fs-5"></i>
                                </div>
                                <div class="d-flex align-items-center">
                                    <span class="fs-5">
                                        @lang('translation.groups.groups')
                                    </span>
                                </div>
                            </div>
                        </div>
                    </a>
                </li>
                <li class="list-group-item">
                    <a href="{{ route('admin.settings.sms') }}" class="text-reset">
                        <div class="gap-2 d-flex align-items-center">
                            <div class="text-white rounded bg-success d-flex justify-content-center align-items-center"
                                style="width: 35px; height: 35px">
                                <i class="ri-messenger-fill fs-5"></i>
                            </div>
                            <div>
                                <span class="fs-5">
                                    @lang('translation.sms.settings')
                                </span>
                            </div>
                        </div>
                    </a>
                </li>
                <li class="list-group-item">
                    <a href="{{ route('admin.settings.leads') }}" class="text-reset">
                        <div class="gap-2 d-flex align-items-center">
                            <div class="text-white rounded bg-success d-flex justify-content-center align-items-center"
                                style="width: 35px; height: 35px">
                                <i class="ri-customer-service-2-fill fs-5"></i>
                            </div>
                            <div>
                                <span class="fs-5">
                                    @lang('translation.leads.settings')
                                </span>
                            </div>
                        </div>
                    </a>
                </li>
                <li class="list-group-item">
                    <a href="{{ route('admin.settings.telegram') }}" class="text-reset">
                        <div class="gap-2 d-flex align-items-center">
                            <div class="text-white rounded bg-success d-flex justify-content-center align-items-center"
                                style="width: 35px; height: 35px">
                                <i class="ri-telegram-fill fs-5"></i>
                            </div>
                            <div>
                                <span class="fs-5">
                                    @lang('translation.telegram_integration')
                                </span>
                            </div>
                        </div>
                    </a>
                </li>
                <li class="list-group-item">
                    <a href="{{ route('admin.quizzes.index') }}" class="text-reset">
                        <div class="gap-2 d-flex align-items-center">
                            <div class="text-white rounded bg-success d-flex justify-content-center align-items-center"
                                style="width: 35px; height: 35px">
                                <i class="ri-microscope-line fs-5"></i>
                            </div>
                            <div>
                                <span class="fs-5">
                                    @lang('translation.assessment_of_students')
                                </span>
                            </div>
                        </div>
                    </a>
                </li>
                <li class="list-group-item">
                    <a href="{{ route('admin.settings.receipts.index') }}" class="text-reset">
                        <div class="gap-2 d-flex align-items-center">
                            <div class="text-white rounded bg-success d-flex justify-content-center align-items-center"
                                style="width: 35px; height: 35px">
                                <i class="ri-bill-line fs-5"></i>
                            </div>
                            <div>
                                <span class="fs-5">
                                    @lang('translation.receipts.settings')
                                </span>
                                <span class="text-white badge bg-danger rounded-pill ms-2">@lang('translation.receipts.new')</span>
                            </div>
                        </div>
                    </a>
                </li>
                <li class="list-group-item">
                    <a href="{{ route('admin.settings.students-telegram-webapp') }}" class="text-reset">
                        <div class="gap-2 d-flex align-items-center">
                            <div class="text-white rounded bg-success d-flex justify-content-center align-items-center"
                                style="width: 35px; height: 35px">
                                <i class="ri-telegram-line fs-5"></i>
                            </div>
                            <div>
                                <span class="fs-5">
                                    @lang('translation.telegram.student_twa_settings')
                                </span>
                                <span class="text-white badge bg-danger rounded-pill ms-2">@lang('translation.new')</span>
                            </div>
                        </div>
                    </a>
                </li>
            </ul>
        </div>
        <div class="col-md-3 col-sm-12"></div>
    </div>
@endsection

@section('script')
    <script>
        document.getElementById('settingsSearch').addEventListener('input', function(e) {
            const searchValue = e.target.value.toLowerCase();
            const listItems = document.querySelectorAll('.list-group-item');

            listItems.forEach(item => {
                const text = item.textContent.toLowerCase();
                if (text.includes(searchValue)) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    </script>
@endsection
