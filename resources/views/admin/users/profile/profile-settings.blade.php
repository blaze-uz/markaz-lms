@extends('layouts.master')
@section('title')
    @lang('translation.settings')
@endsection
@section('css')
    <style>
        #inviteCode.invite-page {
            box-sizing: border-box;
            display: flex;
            flex-direction: row;
            background-color: #fff;
            border: 1px solid #ccc;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
            justify-content: space-between;
            width: 100%;
            box-shadow: 0px 1px 2px rgba(0, 0, 0, 0.07);
        }

        #inviteCode.invite-page #link {
            align-self: center;
            font-size: 1.2em;
            color: #333;
            font-weight: bold;
            flex-grow: 2;
            background-color: #fff;
            border: none;
        }

        /* #inviteCode.invite-page #copy {
                width: 30px;
                height: 30px;
                margin-left: 20px;
                border: 1px solid black;
                border-radius: 5px;
                background-color: #f8f8f8;
            } */

        #inviteCode.invite-page #copy i {
            display: block;
            line-height: 30px;
            position: relative;
        }

        #inviteCode.invite-page #copy i::before {
            display: block;
            width: 15px;
            margin: 0 auto;
        }

        #inviteCode.invite-page #copy i.copied::after {
            position: absolute;
            top: 0px;
            right: 35px;
            height: 30px;
            line-height: 25px;
            display: block;
            content: "copied";
            font-size: 1.5em;
            padding: 2px 10px;
            color: #fff;
            background-color: #333;
            border-radius: 3px;
            opacity: 1;
            will-change: opacity, transform;
            animation: showcopied 1.5s ease;
        }

        @keyframes showcopied {
            0% {
                opacity: 0;
                transform: translateX(100%);
            }

            70% {
                opacity: 1;
                transform: translateX(0);
            }

            100% {
                opacity: 0;
            }
        }
    </style>
@endsection
@section('content')
    {{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}
    <div class="row">
        <div class="col-xxl-3">
            <div class="mb-4">
                <form action="{{ route('admin.change.profile') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="card">
                        <div class="p-4 card-body">
                            <div class="text-center">
                                <div class="mx-auto mb-4 profile-user position-relative d-inline-block">
                                    <img src="@if (auth()->user()->getFirstMediaUrl('avatar')) {{ auth()->user()->getFirstMediaUrl('avatar') }} @else {{ asset('images/markaz-lms.svg') }} @endif"
                                        class="rounded-circle avatar-xl img-thumbnail user-profile-image"
                                        alt="user-profile-image">
                                    <div class="p-0 avatar-xs rounded-circle profile-photo-edit">
                                        <input id="profile-img-file-input" type="file" class="profile-img-file-input"
                                            name="avatar">
                                        <label for="profile-img-file-input" class="profile-photo-edit avatar-xs">
                                            <span class="avatar-title rounded-circle bg-light text-body">
                                                <i class="ri-camera-fill"></i>
                                            </span>
                                        </label>
                                    </div>
                                </div>
                                <h5 class="mb-1 fs-16">{{ auth()->user()->name }}</h5>
                                <p class="mb-0 text-muted">
                                    <span class="badge bg-success-subtle text-success">
                                        {{ auth()->user()->roles->first()->name }}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 card">
                        <div class="card-header">
                            <h5 class="mb-0 card-title">
                                <i class="fas fa-language me-2"></i>
                                @lang('translation.choose_language')
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="gap-2 d-flex justify-content-center">
                                {{-- <a href="{{ url('index/en') }}"
                                class="btn {{ app()->getLocale() == 'en' ? 'btn-primary' : 'btn-outline-primary' }} d-flex align-items-center">
                                <i class="fas fa-flag-usa me-2"></i>
                                <span>English</span>
                            </a> --}}
                                <a href="{{ url('index/ru') }}"
                                    class="btn {{ app()->getLocale() == 'ru' ? 'btn-primary' : 'btn-outline-primary' }} d-flex align-items-center">
                                    <i class="fas fa-landmark me-2"></i>
                                    <span>Русский</span>
                                </a>
                                <a href="{{ url('index/uz') }}"
                                    class="btn {{ app()->getLocale() == 'uz' ? 'btn-primary' : 'btn-outline-primary' }} d-flex align-items-center">
                                    <i class="fas fa-mosque me-2"></i>
                                    <span>O'zbek</span>
                                </a>
                            </div>
                        </div>
                    </div>
                    {{-- <livewire:telegram-connect /> --}}
            </div>
        </div>
        <div class="col-xxl-9">
            <div class="card card-height-100">
                <div class="card-header">
                    <ul class="rounded nav nav-tabs-custom card-header-tabs border-bottom-0" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#personalDetails" role="tab">
                                <i class="fas fa-home"></i>
                                @lang('translation.profile.personal_details')
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#changePassword" role="tab">
                                <i class="far fa-user"></i>
                                @lang('translation.profile.change_password')
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="p-4 card-body">
                    <div class="tab-content">
                        <div class="tab-pane active" id="personalDetails" role="tabpanel">
                            <div class="row">
                                <div class="col-lg-6">
                                    <x-input name="name" label="{{ __('translation.students.full_name') }}"
                                        value="{{ auth()->user()->name }}" />
                                </div>
                                <!--end col-->
                                <div class="col-lg-6">
                                    <x-input name="email" label="{{ __('translation.login') }}"
                                        value="{{ auth()->user()->email }}" />
                                </div>
                                <!--end col-->
                                <div class="col-lg-6">
                                    <x-phone-input name="phone" label="{{ __('translation.users.phone') }}"
                                        value="{{ auth()->user()->phone }}" />
                                </div>
                                <!--end col-->
                                <div class="col-lg-6">
                                    <x-input :label="__('translation.users.birthday')" name="birthday" type="date" :placeholder="__('translation.users.birthday')"
                                        :value="$item->birthday ?? (old('birthday') ?? '')" />
                                </div>
                                <!--end col-->
                                <div class="col-lg-6">
                                    <div class="mb-3 form-group">
                                        <label for="percentage">
                                            @lang('translation.users.percentage')
                                        </label>
                                        <input type="number" id="percentage" class="form-control"
                                            value="{{ auth()->user()->percentage }}" disabled>
                                    </div>
                                </div>
                                <!--end col-->
                            </div>
                            <div class="d-flex justify-content-end">
                                <button class="btn btn-success" type="submit">
                                    @lang('translation.profile.save_changes')
                                </button>
                            </div>
                        </div>
                        </form>
                        <!--end tab-pane-->
                        <div class="tab-pane" id="changePassword" role="tabpanel">
                            <form action="{{ route('admin.change.password') }}" method="POST">
                                @csrf
                                <div class="row g-2">
                                    <div class="col-lg-4">
                                        <div>
                                            <label for="newpasswordInput" class="form-label">@lang('translation.profile.new_password')</label>
                                            <input type="password"
                                                class="form-control @error('new_password') is-invalid @enderror"
                                                id="newpasswordInput" name="password"
                                                placeholder="{{ __('translation.profile.new_password') }}">
                                            @error('new_password')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <!--end col-->
                                    <div class="col-lg-4">
                                        <div>
                                            <label for="confirmpasswordInput" class="form-label">@lang('translation.profile.confirm_password')</label>
                                            <input type="password"
                                                class="form-control @error('confirm_new_password') is-invalid @enderror"
                                                id="confirmpasswordInput" name="confirm_password"
                                                placeholder="{{ __('translation.profile.confirm_password') }}">
                                            @error('confirm_new_password')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <!--end col-->
                                <div class="col-lg-12">
                                    <div class="text-end">
                                        <button type="submit" class="btn btn-success">
                                            @lang('translation.profile.save_changes')
                                        </button>
                                    </div>
                                </div>
                                <!--end col-->
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!--end tab-pane-->
        </div>
    </div>
    <!--end row-->
    <!-- Modal -->
    <div class="modal fade" id="openTwaModal" tabindex="-1" aria-labelledby="openTwaModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="openTwaModalLabel">
                        @lang('translation.redirect-to-telegram-app')
                    </h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="inviteCode" class="invite-page">
                        <input id="link" value="{{ $twa_key }}" readonly>
                        <div id="copy" class="btn btn-sm btn-primary">
                            <i class="fa fa-clipboard" aria-hidden="true" data-copytarget="#link"></i>
                        </div>
                    </div>

                    <p>
                        @lang('translation.redirect-to-telegram-app-desc')
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        @lang('translation.Close')
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script src="{{ asset('assets/js/profile-setting.init.js') }}"></script>
    <script>
        @if ($errors->any())
            @foreach ($errors->all() as $error)
                window.notifyMessage(`{!! $error !!}`, 'danger')
            @endforeach
        @endif
        // trigger copy event on click
        $('#copy').on('click', function(event) {
            console.log(event);
            copyToClipboard(event);
        });

        // event handler
        function copyToClipboard(e) {
            // alert('this function was triggered');
            // find target element
            var
                t = e.target,
                c = t.dataset.copytarget,
                inp = (c ? document.querySelector(c) : null);
            console.log(inp);
            // check if input element exist and if it's selectable
            if (inp && inp.select) {
                // select text
                inp.select();
                try {
                    // copy text
                    document.execCommand('copy');
                    inp.blur();

                    // copied animation
                    t.classList.add('copied');
                    setTimeout(function() {
                        t.classList.remove('copied');
                    }, 1500);
                } catch (err) {
                    //fallback in case exexCommand doesnt work
                    alert('please press Ctrl/Cmd+C to copy');
                }

            }
        }
    </script>
@endsection
