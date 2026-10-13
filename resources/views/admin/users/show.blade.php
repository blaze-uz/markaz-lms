@extends('layouts.master')

@section('title')
    {{ $item->name }}
@stop

@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.css"
        integrity="sha512-H9jrZiiopUdsLpg94A333EfumgUBpO9MdbxStdeITo+KEIMaNfHNvwyjjDJb+ERPaRS6DpyRlKbvPUasNItRyw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        .profile-wid-bg {
            background: url("{{ asset('images/staff-background.webp') }}");
            background-size: cover;
        }

        .profile-wid-bg:before {
            opacity: .97 !important;
        }
    </style>
@stop

@section('content')
    {{--
<livewire:user.debt-students-list :teacherId="$item->id" /> --}}
    <div class="profile-foreground position-relative mx-n4 mt-n4">
        <div class="profile-wid-bg"></div>
    </div>
    <div class="pt-4 mb-4 mb-lg-3 pb-lg-4 profile-wrapper">
        <div class="p-3 row g-4">
            <div class="col-auto">
                <div class="avatar-lg">
                    @if (!$item->getFirstMediaUrl('avatar'))
                        {!! Avatar::create($item->name)->setTheme('grayscale-light')->setDimension(100)->setFontSize(35)->toSvg() !!}
                    @else
                        <a href="{{ $item->getFirstMediaUrl('avatar') }}" class="instruction-image" alt="staff-avatar">
                            <div
                                style="
                        height: 100px;
                        border-radius: 50%;
                        width: 100px !important;
                        background-image: url('{{ $item->getFirstMediaUrl('avatar') }}') !important;
                        background-size: cover; background-position: center; object-fit: contain">
                            </div>
                        </a>
                    @endif
                </div>
            </div>
            <!--end col-->
            <div class="col">
                <div class="p-2">
                    <h3 class="mb-1 text-white">{{ $item->name }}</h3>
                    <p class="text-white text-opacity-75">
                        @foreach ($item->roles as $role)
                            {{ $role->name }}
                        @endforeach
                    </p>
                    <div class="gap-1 text-white text-opacity-75 hstack">
                        <div class="me-2">
                            <a href="tel:{{ $item->phone }}" class="text-white text-opacity-75">
                                <i class="ri-phone-line"></i> {{ $item->getFormattedPhone() }}
                            </a>
                        </div>
                        <div>
                            <i class="ri-cake-2-line"></i> {{ dateFormat($item->birthday) }}
                            ({{ $item->age }} {{ __('attr.years_old') }})
                        </div>
                    </div>
                </div>
            </div>
            <!--end col-->
            <div class="order-last col-12 col-lg-auto order-lg-0">
                <div class="text-center row text text-white-50">
                    <div class="col-lg-4 col-4">
                        <div class="p-2">
                            <h4 class="mb-1 text-white">
                                {{ $studentsCount }}
                            </h4>
                            <p class="mb-0 fs-14">
                                {{ __('translation.students.students') }}
                            </p>
                        </div>
                    </div>
                    <div class="col-lg-4 col-4">
                        <a href="{{ route('admin.groups.index', ['teacher_id' => $item->id]) }}" class="text-reset">
                            <div class="p-2">
                                <h4 class="mb-1 text-white">
                                    {{ $groups->count() }}
                                </h4>
                                <p class="mb-0 fs-14">
                                    {{ __('translation.groups.groups') }}
                                </p>
                            </div>
                        </a>
                    </div>
                    <div class="col-lg-4 col-4">
                        <div class="p-2">
                            <h4 class="mb-1 text-white">
                                <i class="ri-star-fill text-warning"></i> {{ $item->stars ?? 0 }}
                            </h4>
                            <p class="mb-0 fs-14">
                                {{ __('translation.users.stars_count') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <!--end col-->

        </div>
        <!--end row-->
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div>
                <div class="flex-wrap d-flex profile-wrapper">
                    <!-- Nav tabs -->
                    <ul class="gap-2 nav nav-pills animation-nav profile-nav gap-lg-3 flex-grow-1" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a class="nav-link fs-14 active" data-bs-toggle="tab" href="#overview-tab" role="tab"
                                aria-selected="true">
                                <span>
                                    {{ __('translation.users.overview') }}
                                </span>
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link fs-14" data-bs-toggle="tab" href="#salaries" role="tab"
                                aria-selected="false" tabindex="-1">
                                <span>
                                    @lang('translation.users.salaries')
                                </span>
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link fs-14" data-bs-toggle="tab" href="#payments" role="tab"
                                aria-selected="false" tabindex="-1">
                                <span>
                                    @lang('translation.students.payments')
                                </span>
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link fs-14" data-bs-toggle="tab" href="#penalties" role="tab"
                                aria-selected="false" tabindex="-1">
                                <span>
                                    @lang('translation.users.penalties')
                                </span>
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link fs-14" data-bs-toggle="tab" href="#messages" role="tab"
                                aria-selected="false" tabindex="-1">
                                <span>
                                    @lang('translation.users.messages')
                                </span>
                            </a>
                        </li>
                    </ul>
                    <div class="flex-wrap gap-2 d-flex align-items-center">
                        <div class="dropdown">
                            <button class="btn bg-primary-subtle text-primary dropdown-toggle rounded-pill" type="button"
                                id="dropdownSendMessage" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="mdi mdi-message"></i>
                                @lang('translation.users.send_message')
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="dropdownSendMessage">
                                <li>
                                    <a class="dropdown-item"
                                        href="{{ route('admin.sms.create', ['sendSmsIds' => $item->id, 'sendSmsType' => 'staff']) }}">
                                        @lang('translation.users.send_sms')
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="#" data-bs-toggle="modal"
                                        data-bs-target="#sendTelegramModal">
                                        @lang('translation.users.send_telegram')
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <button type="button" class="btn bg-primary-subtle text-primary text-nowrap rounded-pill" data-bs-toggle="modal"
                            data-bs-target="#myModal">
                            <span class="mdi mdi-cash-multiple"></span>
                            @lang('translation.students.payment')
                        </button>
                        <a class="btn bg-primary-subtle text-primary text-nowrap rounded-pill" data-bs-toggle="modal"
                            data-bs-target="#penaltyModal" href="{{ route('admin.users.edit', ['user' => $item->id]) }}">
                            <span class="mdi mdi-account-alert"></span>
                            @lang('translation.users.add_penalty')
                        </a>
                        <a href="{{ route('admin.users.edit', $item->id) }}" class="btn btn-success text-nowrap rounded-pill">
                            <i class="align-bottom ri-settings-3-line"></i> {{ __('translation.users.settings') }}
                        </a>
                    </div>
                    </div>
                </div>
                <!-- Tab panes -->
                <div class="pt-4 tab-content text-muted">
                    <div class="tab-pane active" id="overview-tab" role="tabpanel">
                        <div class="row">
                            <div class="col-xxl-4">
                                <livewire:user.user-student-payments-card :user="$item" />

                                <div class="card rounded-4">
                                    <div class="card-body">
                                        <h5 class="text-muted text-uppercase fs-13">
                                            @lang('translation.students.balance')
                                            @if ($balance < 0)
                                                (@lang('attr.debt'))
                                            @endif
                                        </h5>
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0">
                                                <i @class([
                                                    'ri-exchange-dollar-line display-6',
                                                    'text-danger' => $balance < 0,
                                                    'text-success' => $balance > 0,
                                                    'text-muted' => $balance == 0,
                                                ])>
                                                </i>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <h3 class="mb-0">
                                                    <span @class([
                                                        'text-danger' => $balance < 0,
                                                        'text-success' => $balance > 0,
                                                        'text-muted' => $balance == 0,
                                                    ])>
                                                        {{ money(abs($balance)) }}
                                                    </span>
                                                </h3>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card rounded-4">
                                    <div class="card-body">
                                        <h5 class="mb-3 card-title">{{ __('translation.users.info') }}</h5>
                                        <div class="table-responsive">
                                            <table class="table mb-0 table-borderless">
                                                <tbody>
                                                    <tr>
                                                        <th class="ps-0" scope="row">@lang('translation.users.full_name')
                                                            :
                                                        </th>
                                                        <td class="text-muted">
                                                            {{ $item->name }}
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th class="ps-0" scope="row">@lang('translation.users.phone') :</th>
                                                        <td class="text-muted">
                                                            <a href="tel:{{ $item->phone }}">
                                                                {{ $item->getFormattedPhone() }}
                                                            </a>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th class="ps-0" scope="row">@lang('translation.users.percentage')
                                                            :
                                                        </th>
                                                        <td class="text-muted">
                                                            {{ $item->percentage }}%
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th class="ps-0" scope="row">@lang('translation.users.stars') :</th>
                                                        <td class="text-muted">
                                                            <i class="ri-star-fill text-warning"></i> {{ $item->stars ?? 0 }} {{ __('translation.users.stars') }}
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th class="ps-0" scope="row">@lang('translation.account') :</th>
                                                        <td class="text-muted">
                                                            <a href="{{ route('admin.accounts.show', $item->account_id) }}"
                                                                target="_blank">
                                                                {{ $item->account?->name ?? '-' }}
                                                                <i class="mdi mdi-open-in-new"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th class="ps-0" scope="row">@lang('translation.users.email'):</th>
                                                        <td class="text-muted">
                                                            {{ $item->email }}
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <div class="card rounded-4">
                                    <div class="card-body">
                                        <h5 class="mb-4 card-title">
                                            @lang('translation.users.branches')
                                        </h5>
                                        <div class="flex-wrap gap-2 d-flex fs-15">
                                            @foreach ($item->branches as $branch)
                                                <span class="badge bg-primary-subtle text-primary">
                                                    {{ $branch->name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div><!-- end card body -->
                                </div>
                            </div>
                            <!--end col-->
                            <div class="col-xxl-8">
                                <div class="card rounded-4">
                                    <div class="card-body">
                                        <livewire:user.groups-list :user="$item" />
                                    </div>
                                    <!--end card-body-->
                                </div>
                                <livewire:teacher-student-statistics-by-month :user="$item" />
                                {{-- <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-3">
                                        @lang('translation.students.payments')
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <livewire:teacher-student-payments-table :teacher="$item" />
                                </div>
                                <!--end card-body-->
                            </div> --}}
                            </div>
                            <!--end col-->
                        </div>
                        <livewire:group.attendance-table :user="$item" lazy />
                        @can('report-educational')
                            @if ($item->hasRole('Teacher'))
                                <livewire:report.homework-report :lock-teacher-id="$item->id" lazy />
                            @endif
                        @endcan
                        <livewire:user.user-calendar :user="$item" />
                    </div>
                    <div class="tab-pane fade" id="salaries" role="tabpanel">
                        <div class="card rounded-4">
                            <div class="card-body">
                                <h5 class="mb-3 card-title">
                                    @lang('translation.users.salaries')
                                </h5>
                                <livewire:user.salary-table :user="$item" />
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="payments" role="tabpanel">
                        <div class="card rounded-4">
                            <div class="card-body">
                                <livewire:user.payment-table :user="$item" />
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="penalties" role="tabpanel">
                        <div class="card rounded-4">
                            <div class="card-body">
                                <livewire:user.penalty-table :user="$item" />
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="messages" role="tabpanel">
                        <div class="card rounded-4">
                            <div class="card-body">
                                <livewire:user.message-table :user="$item" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!--payment modal-->
    <div id="myModal" class="modal fade" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true"
        style="display: none;">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="myModalLabel">
                        @lang('translation.users.payment')
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.users.payment', $item->id) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        @include('admin.users.partials.payment-form')
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                            @lang('translation.Close')
                        </button>
                        <x-button id="detach-btn" class="btn-primary">
                            @lang('translation.btn.save')
                        </x-button>
                    </div>
                </form>

            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->

    <!-- Send Telegram Message Modal -->
    <div class="modal fade" id="sendTelegramModal" tabindex="-1" aria-labelledby="sendTelegramModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="sendTelegramModalLabel">
                        @lang('translation.users.send_telegram')
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.users.send-telegram-message', $item->id) }}" method="POST"
                    id="sendTelegramForm">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="telegram_message" class="form-label">@lang('translation.telegram.message')</label>
                            <textarea class="form-control" id="telegram_message" name="message" rows="4" required></textarea>
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

    <!--payment modal-->
    <div id="penaltyModal" class="modal fade" tabindex="-1" aria-labelledby="penaltyLabel" aria-hidden="true"
        style="display: none;">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="penaltyLabel">
                        @lang('translation.users.add_penalty')
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.users.add-penalty', $item->id) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        @include('admin.users.partials.penalty-form')
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                            @lang('translation.Close')
                        </button>
                        <button class="btn btn-primary" type="submit">
                            @lang('translation.btn.save')
                        </button>
                    </div>
                </form>

            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
@stop
@section('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.js"></script>
    <script>
        $(".instruction-image").fancybox({
            'transitionIn': 'elastic',
            'transitionOut': 'elastic',
            'speedIn': 600,
            'speedOut': 200,
            'overlayShow': false
        });
        @if ($errors->any())
            @foreach ($errors->all() as $error)
                window.notifyMessage(`{!! $error !!}`, 'danger')
            @endforeach
        @endif
        tippy('.students_payment_percentage_info', {
            content: "{!! __('translation.users.students_payment_percentage_info') !!}",
        })
    </script>
@endsection
