@extends('layouts.master')

@section('title')
    {{ $item->getFullName() }}
@stop

@section('content')
    {{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}
    <div class="row">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="p-4 card-body">
                    <div>
                        <div>
                            <div class="mx-auto d-flex justify-content-between align-items-center w-100">
                                <a href="{{ route('admin.students.index') }}"
                                    class="rounded border btn btn-light d-flex justify-content-center align-items-center t-back-btn"
                                    style="height: 35px; width:35px">
                                    <i class="mdi mdi-arrow-left"></i>
                                </a>
                                @if ($item->trashed())
                                    <img src="{{ asset('assets/images/deleted.png') }}" alt="deleted student img"
                                        width="100">
                                @else
                                    {!! Avatar::create($item->getFullName())->setTheme('grayscale-light')->setDimension(80)->setFontSize(35)->toSvg() !!}
                                @endif
                                <div class="dropdown">
                                    <button
                                        class="rounded border btn btn-light d-flex justify-content-center align-items-center t-more-btn"
                                        type="button" data-bs-toggle="dropdown" aria-expanded="false"
                                        style="height: 35px; width:35px">
                                        <i class="ri-more-2-fill"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        @if (!$item->trashed())
                                            @can('student-attach-group')
                                                <a
                                                    href="{{ route('admin.groups.attach-student.view', ['student_id' => $item->id]) }}">
                                                    <li class="dropdown-item">
                                                        <i class="mdi mdi-clipboard-plus-outline"></i>
                                                        @lang('translation.groups.attach')
                                                    </li>
                                                </a>
                                            @endcan
                                            @if ($item->groups->count() > 0)
                                                @can('student-transfer-group')
                                                    <a href="{{ route('admin.students.transfer-group-view', $item->id) }}">
                                                        <li class="dropdown-item">
                                                            <span class="mdi mdi-transit-transfer"></span>
                                                            @lang('translation.students.transfer_group')
                                                        </li>
                                                    </a>
                                                @endcan
                                            @endif
                                            @can('student-send-sms')
                                                <a
                                                    href="{{ route('admin.sms.create', ['sendSmsIds' => json_encode([$item->id])]) }}">
                                                    <li class="dropdown-item">
                                                        <i class="mdi mdi-message-text-outline me-1"></i>
                                                        @lang('translation.users.send_sms')
                                                    </li>
                                                </a>
                                            @endcan
                                            {{-- @can('student-add-debt') --}}
                                            <a href="{{ route('admin.students.add-debt.view', $item->id) }}">
                                                <li class="dropdown-item">
                                                    <i class="mdi mdi-account-alert me-1"></i>
                                                    @lang('translation.students.add_debt')
                                                </li>
                                            </a>
                                            {{-- @endcan --}}
                                            @can('student-delete')
                                                <li class="cursor-pointer dropdown-item text-danger" data-bs-toggle="modal"
                                                    data-bs-target="#deleteModal">
                                                    <i class="mdi mdi-delete"></i>
                                                    @lang('translation.btn.delete')
                                                </li>
                                            @endcan
                                        @else
                                            <li class="dropdown-item">
                                                @lang('translation.students.deleted')!
                                            </li>
                                        @endif

                                    </ul>
                                </div>
                            </div>
                            <div class="mt-4 text-center">
                                <h5 class="mb-1">
                                    {{ $item->getFullName() }}
                                    @if ($item->trashed())
                                        <span class="badge bg-danger rounded-pill">@lang('translation.students.deleted')</span>
                                    @endif
                                </h5>
                                <p class="text-muted">@lang('attr.create_date') {{ dateFormat($item->created_at) }}</p>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table mb-0 table-borderless">
                                <tbody>
                                    <tr>
                                        <th><span class="fw-medium">@lang('translation.students.phone')</span>
                                        </th>
                                        <td class="text-nowrap">
                                            <div class="d-flex flex-column">
                                                <a class="text-reset"
                                                    href="tel:{{ $item->phone }}">{{ $item->getFormattedPhone() }}</a>
                                                @if ($familiarsCount > 1)
                                                    <form action="{{ route('admin.students.index') }}" target="_blank">
                                                        <input type="hidden" name="phone" value="{{ $item->phone }}">
                                                        <small>
                                                            <button type="submit" class="p-0 m-0 btn text-primary"
                                                                style="font-size: 12px">
                                                                Yana {{ $familiarsCount - 1 }} ta o'quvchi topildi
                                                                <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                                            </button>
                                                        </small>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @if ($item->parent_phone)
                                        <tr>
                                            <th style="width: 200px">
                                                <span class="fw-medium">@lang('translation.students.parent_phone')</span>
                                            </th>
                                            <td class="text-nowrap">
                                                <a class="text-reset" href="tel:{{ $item->parent_phone }}">
                                                    {{ $item->getFormattedPhone(true) }}
                                                </a>
                                            </td>
                                        </tr>
                                    @endif
                                    @if ($item->phone2)
                                        <tr>
                                            <th><span class="fw-medium">@lang('translation.students.phone2')</span></th>
                                            <td class="text-nowrap">
                                                <a class="text-reset" href="tel:{{ $item->phone2 }}">{{ $item->getFormattedPhone2() }}</a>
                                            </td>
                                        </tr>
                                    @endif
                                    @if ($item->address)
                                        <tr>
                                            <th><span class="fw-medium">@lang('translation.students.address')</span></th>
                                            <td>{{ $item->address }}</td>
                                        </tr>
                                    @endif
                                    <tr>
                                        <th><span class="fw-medium">@lang('attr.gender')</span>
                                        </th>
                                        <td class="gap-1 text-nowrap d-flex align-items-center">
                                            @if ($item->gender->value == 'male')
                                                <span class="badge bg-primary-subtle text-primary">
                                                    <i class="ri-men-line"></i>
                                                </span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger">
                                                    <i class="ri-women-line"></i>
                                                </span>
                                            @endif
                                            {{ $item->gender->getText() }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th><span class="fw-medium">@lang('translation.students.birthday')</span>
                                        </th>
                                        <td class="text-nowrap">
                                            {{ dateFormat($item->birthday) }}
                                            @if ($is_today_birthday)
                                                🎂
                                                <span class="badge bg-success-subtle text-success">
                                                    {{ __('translation.students.birthday_today') }}
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th><span class="fw-medium">@lang('translation.users.account')</span></th>
                                        <td>
                                            <a href="{{ route('admin.accounts.show', $item->account_id) }}"
                                                target="_blank">
                                                {{ $item->account?->name ?? 'Hisob nofaol' }}
                                                <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th><span class="fw-medium">@lang('translation.students.comment')</span>
                                        </th>
                                        <td>{!! $item->comment ?? '-' !!}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4 row">
                            @if (!$item->trashed())
                                <div class="col-6">
                                    @can('student-edit')
                                        <a class="w-100 btn btn-warning ounded-3"
                                            href="{{ route('admin.students.edit', $item->id) }}">
                                            <i class="mdi mdi-pencil"></i>
                                            @lang('translation.btn.edit')
                                        </a>
                                    @endcan
                                </div>
                            @endif
                            <div class="col-6">
                                @if (!$item->trashed())
                                    @can('student-payment')
                                        <a href="{{ route('admin.students.payment.view', $item->id) }}"
                                            class="rounded btn btn-primary w-100 rounded-3">
                                            <span class="mdi mdi-cash-multiple"></span>
                                            @lang('translation.students.payment')
                                        </a>
                                    @endcan
                                @endif
                            </div>
                        </div>
                        @if ($item->trashed())
                            <div class="mt-3 row">
                                <div class="col-6">
                                    <form action="{{ route('admin.students.restore', $item->id) }}" class="w-100"
                                        method="POST">
                                        @csrf
                                        <button type="submit" onclick="return confirm('@lang("
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            translation.students.restore_confirm")')"
                                            class="btn btn-danger d-flex align-items-center justify-content-center btn-sm w-100">
                                            <i class="mdi mdi-delete-restore fs-4"></i>
                                            @lang('translation.students.restore')
                                        </button>
                                    </form>
                                </div>
                                <div class="col-6">
                                    <form action="{{ route('admin.students.force-delete', $item->id) }}" class="w-100"
                                        method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" onclick="return confirm('@lang("
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            translation.students.delete_confirm")')"
                                            class="btn btn-danger d-flex align-items-center justify-content-center btn-sm w-100">
                                            <i class="mdi mdi-delete fs-4"></i>
                                            @lang('translation.students.delete_permanently')
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <!--end card-->
        </div>
        <div class="col-md-8">
            <!-- Statistics Cards -->
            <div class="mb-3 row g-2 g-md-3" style="display: flex; flex-wrap: wrap;">
                @can('student-balance')
                    <!-- Balance Card -->
                    <div class="col-md-4 col-sm-6 mb-0" style="display: flex;">
                        <div class="card mb-0 stat-card" style="flex: 1;">
                            <div class="card-body p-3 d-flex flex-column">
                                <div class="d-flex align-items-center justify-content-between mb-1 flex-grow-1">
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <p class="mb-0 fw-medium text-muted" style="font-size: 0.85rem;">
                                                @lang('translation.students.balance')
                                            </p>
                                            @if ($item->account?->getBalance() < 0)
                                                <span class="badge bg-danger-subtle text-danger"
                                                    style="font-size: 0.7rem; padding: 0.15rem 0.35rem;">
                                                    @lang('translation.students.debt')
                                                </span>
                                            @endif
                                        </div>
                                        <h3 class="mb-0 fw-bold fs-3">
                                            <span
                                                @class([
                                                    'text-danger' => $item->account?->getBalance() < 0,
                                                    'text-success' => $item->account?->getBalance() > 0,
                                                    'text-muted' => $item->account?->getBalance() == 0,
                                                ])>{{ money($item->account?->getBalance()) }}</span>
                                        </h3>
                                    </div>
                                    <div class="flex-shrink-0">
                                        <div class="stat-icon">
                                            <i class="ri-exchange-dollar-line"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-auto">
                                    <form action="{{ route('admin.students.refresh-balance', $item->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="border-0 bg-transparent text-muted">
                                            <i class="mdi mdi-refresh"></i>@lang('translation.students.refresh')
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Loan Card -->
                    <div class="col-md-4 col-sm-6 mb-0" style="display: flex;">
                        <div class="card mb-0 stat-card" style="flex: 1;">
                            <div class="card-body p-3 d-flex flex-column">
                                <div class="d-flex align-items-center justify-content-between flex-grow-1">
                                    <div class="flex-grow-1">
                                        <p class="mb-1 fw-medium text-muted" style="font-size: 0.85rem;">
                                            @lang('translation.students.loan')
                                        </p>
                                        <h3 class="mb-0 fw-bold fs-3">
                                            <span class="text-danger counter-value" data-target="{{ $debt_amount }}">
                                                {{ money($debt_amount) }}
                                            </span>
                                        </h3>
                                    </div>
                                    <div class="flex-shrink-0">
                                        <div class="stat-icon">
                                            <i class="ri-money-dollar-circle-line"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-auto">
                                    <a class="text-muted" style="font-size: 0.75rem;">
                                        <i class="mdi mdi-arrow-up-right-thick me-1"></i>@lang('translation.students.loan')
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endcan

                <!-- Attendance Card -->
                <div class="col-md-4 col-sm-12 mb-0" style="display: flex;">
                    <div class="card mb-0 stat-card" style="flex: 1;">
                        <div class="card-body p-3 d-flex flex-column">
                            <div class="d-flex align-items-center justify-content-between flex-grow-1">
                                <div class="flex-grow-1">
                                    <p class="mb-1 fw-medium text-muted" style="font-size: 0.85rem;">
                                        @lang('translation.groups.attendance')
                                    </p>
                                    <h3 class="mb-0 fw-bold fs-3">
                                        <div class="cursor-pointer d-flex gap-1 align-items-center">
                                            <span data-bs-toggle="tooltip"
                                                title="{{ __('translation.students.attendance_count') }}"
                                                class="counter-value">
                                                {{ $attendance['attendanceCount'] }}
                                            </span>
                                            <span class="text-muted">/</span>
                                            <span data-bs-toggle="tooltip"
                                                title="{{ __('translation.students.present_count') }}"
                                                class="text-success counter-value">{{ $attendance['presentCount'] }}</span>
                                            <span class="text-muted">/</span>
                                            <span data-bs-toggle="tooltip"
                                                title="{{ __('translation.students.absent_count') }}"
                                                class="text-danger counter-value">{{ $attendance['absentCount'] }}</span>
                                        </div>
                                    </h3>
                                </div>
                                <div class="flex-shrink-0">
                                    <div class="stat-icon">
                                        <i class="ri-calendar-check-line"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-auto">
                                <small class="text-muted d-block"
                                    style="font-size: 0.7rem; margin-top: 0.25rem;">{{ dateFormat(now(), 'F Y', session('lang')) }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div><!-- end row -->

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body">
                    <!-- Nav tabs -->
                    <ul class="nav nav-tabs nav-tabs-custom nav-justified" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a class="nav-link active" data-bs-toggle="tab" href="#home1" role="tab"
                                aria-selected="true">
                                @lang('translation.groups.groups')
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" data-bs-toggle="tab" href="#attendance" role="tab"
                                aria-selected="false" tabindex="-1">
                                @lang('translation.groups.attendance')
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" data-bs-toggle="tab" href="#payments" role="tab"
                                aria-selected="false" tabindex="-1">
                                @lang('translation.students.payments')
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" data-bs-toggle="tab" href="#payment-status" role="tab"
                                aria-selected="false" tabindex="-1">
                                @lang('attr.debts')
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" data-bs-toggle="tab" href="#student-reminders" role="tab"
                                aria-selected="false" tabindex="-1">
                                @lang('translation.students.reminders_tab')
                            </a>
                        </li>

                        @if ($lead_data)
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" data-bs-toggle="tab" href="#lead-info" role="tab"
                                    aria-selected="false" tabindex="-1">
                                    @lang('translation.students.lead_data')
                                </a>
                            </li>
                        @endif
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane active show" id="home1" role="tabpanel">
                            @if ($item->trashed())
                                <div class="p-5 d-flex flex-column justify-content-center align-items-center">
                                    <h3 class="mb-4">@lang('translation.students.deleted')!</h3>
                                    @if ($deleteInfo)
                                        <table class="table">
                                            <tr>
                                                <th>@lang('translation.students.deleted_by')</th>
                                                <td>{{ $deleteInfo->deletedBy->name ?? '-' }}</td>
                                            </tr>
                                            <tr>
                                                <th>@lang('translation.deleted_at')</th>
                                                <td>{{ dateFormat($deleteInfo->deleted_at) }}</td>
                                            </tr>
                                            <tr>
                                                <th>@lang('translation.students.comment')</th>
                                                <td>{{ $deleteInfo->comment }}</td>
                                            </tr>
                                        </table>
                                    @endif
                                </div>
                            @else
                                <div class="table-responsive">
                                    <table class="table">
                                        <tr>
                                            <th>ID</th>
                                            <th>@lang('translation.groups.name')</th>
                                            <th>@lang('translation.students.attached_date')</th>
                                            <th></th>
                                        </tr>
                                        @forelse($groups as $group)
                                            <tr>
                                                <td>{{ $group->id }}</td>
                                                <td class="text-nowrap">
                                                    <a href="{{ route('admin.groups.show', $group->id) }}">
                                                        {{ $group->name }}
                                                    </a>
                                                </td>
                                                <td class="text-nowrap">
                                                    {{ dateFormat($group->pivot->attached_at) }}
                                                </td>
                                                <td class="text-nowrap">
                                                    @canany(['group-detach-student', 'student-detach-group'])
                                                        <form action="{{ route('admin.groups.detach') }}" method="POST"
                                                            class="d-inline-block">
                                                            @csrf
                                                            <input type="hidden" name="student_id"
                                                                value="{{ $item->id }}">
                                                            <input type="hidden" name="group_id"
                                                                value="{{ $group->id }}">
                                                            <button type="submit" data-bs-toggle="tooltip"
                                                                data-bs-trigger="hover" data-bs-placement="top"
                                                                title="@lang('translation.groups.detach')" class="p-0 btn btn-sm"
                                                                style="padding-right: .5rem !important;">
                                                                <i class="mdi mdi-account-remove fs-5 text-muted"></i>
                                                            </button>
                                                        </form>
                                                    @endcanany
                                                    <x-action-btn.buttons :showable="true" :editable="false"
                                                        :deletable="false" :show-url="route('admin.groups.show', ['group' => $group->id])">
                                                    </x-action-btn.buttons>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="100">
                                                    <x-no-result />
                                                </td>
                                            </tr>
                                        @endforelse
                                    </table>
                                </div>
                            @endif
                        </div>
                        <div class="mt-3 tab-pane" id="attendance" role="tabpanel">
                            <livewire:student.attendance-table :student="$item" />
                        </div>
                        <div class="mt-3 tab-pane" id="payments" role="tabpanel">
                            <livewire:student.payment-table :student="$item" />
                        </div>
                        <div class="tab-pane" id="payment-status" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table">
                                    <tr>
                                        <th>ID</th>
                                        <th>@lang('translation.groups.group')</th>
                                        <th>@lang('translation.groups.debt_range')</th>
                                        <th>@lang('attr.amount')</th>
                                        <th>@lang('attr.left_amount')</th>
                                        <th>@lang('attr.payment_status')</th>
                                        <th></th>
                                    </tr>
                                    @forelse($studentGroupPayments as $studentGroupPayment)
                                        <tr>
                                            <th>{{ $studentGroupPayment->id }}</th>
                                            <td class="text-nowrap">
                                                @if ($studentGroupPayment->group?->deleted_at)
                                                    <del data-bs-toggle="tooltip"
                                                        title="{{ __('translation.groups.deleted') }}"
                                                        class="cursor-pointer">
                                                        {{ $studentGroupPayment->group?->name ?? __('translation.groups.deleted') }}
                                                    </del>
                                                @else
                                                    {{ $studentGroupPayment->group?->name ?? __('translation.groups.deleted') }}
                                                @endif
                                            </td>
                                            <td class="text-nowrap">
                                                @if ($studentGroupPayment->group?->payment_type == 'date_to_date')
                                                    {{ debtRange($studentGroupPayment->range) }}
                                                @elseif ($studentGroupPayment->group?->payment_type == 'one_time')
                                                    <span class="badge bg-success-subtle text-success">
                                                        @lang('translation.groups.one_time')
                                                    </span>
                                                @else
                                                    {{ $studentGroupPayment->range }}
                                                @endif
                                            </td>
                                            <td class="text-nowrap">{{ money($studentGroupPayment->amount) }}</td>
                                            <td class="text-nowrap">
                                                {{ money($studentGroupPayment->left_amount) }}
                                            </td>
                                            <td>
                                                @if (!is_null($studentGroupPayment->left_amount))
                                                    <span @class([
                                                        'badge',
                                                        'text-danger bg-danger-subtle' => $studentGroupPayment->left_amount > 0,
                                                        'text-success bg-success-subtle' => $studentGroupPayment->left_amount <= 0,
                                                    ])>
                                                        {{ $studentGroupPayment->left_amount > 0 ? __('attr.not_paid') : __('attr.paid') }}
                                                    </span>
                                                @else
                                                    <span @class([
                                                        'badge',
                                                        'text-danger bg-danger-subtle' => $studentGroupPayment->left_amount > 0,
                                                        'text-success bg-success-subtle' => $studentGroupPayment->left_amount <= 0,
                                                    ])>
                                                        {{ $studentGroupPayment->left_amount > 0 ? __('attr.not_paid') : __('attr.paid') }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($studentGroupPayment->left_amount > 0)
                                                    @can('group-update-debt')
                                                        <a href="{{ route('admin.groups.edit-debt', $studentGroupPayment->id) }}"
                                                            data-bs-toggle="tooltip"
                                                            title="{{ __('translation.groups.edit-debt') }}">
                                                            <i class="mdi mdi-pencil"></i>
                                                        </a>
                                                    @endcan
                                                @else
                                                    @can('student-update-paid-debt-amount')
                                                        <a href="{{ route('admin.groups.edit-debt', $studentGroupPayment->id) }}"
                                                            data-bs-toggle="tooltip"
                                                            title="{{ __('translation.groups.edit-debt') }}">
                                                            <i class="mdi mdi-pencil"></i>
                                                        </a>
                                                    @endcan
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="100">
                                                <x-no-result />
                                            </td>
                                        </tr>
                                    @endforelse
                                </table>
                                {{ $studentGroupPayments->links() }}
                            </div>
                        </div>

                        <div class="mt-3 tab-pane" id="student-reminders" role="tabpanel">
                            @if ($item->trashed())
                                <div class="p-4 text-center text-muted">
                                    @lang('translation.students.reminders_unavailable_trashed')
                                </div>
                            @else
                                <livewire:student.student-reminders :student="$item" :key="'student-reminders-' . $item->id" />
                            @endif
                        </div>

                        @if ($lead_data)
                            <div class="tab-pane" id="lead-info" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table">
                                        <tr>
                                            <th>@lang('translation.leads.name') @lang('translation.leads.surname')</th>
                                            <td>{{ $lead_data->name }}</td>
                                        </tr>
                                        <tr>
                                            <th>@lang('translation.leads.phone')</th>
                                            <td>{{ $lead_data->phone }}</td>
                                        </tr>
                                        <tr>
                                            <th>@lang('translation.leads.creator')</th>
                                            <td>{{ $lead_data->causer->name ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>@lang('translation.leads.created_at')</th>
                                            <td>{{ dateFormat($lead_data->created_at) }}</td>
                                        </tr>
                                        @if ($lead_data->comment)
                                            <tr>
                                                <th>@lang('translation.students.comment')</th>
                                                <td>{{ $lead_data->comment }}</td>
                                            </tr>
                                        @endif
                                        @if ($lead_data->chosen_days)
                                            <tr>
                                                <th>@lang('translation.leads.chosen_days')</th>
                                                <td>
                                                    <span class="badge bg-info">
                                                        {{ __('translation.leads.' . $lead_data->chosen_days) }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endif
                                        @if ($lead_data->chosen_time)
                                            <tr>
                                                <th>@lang('translation.leads.chosen_time')</th>
                                                <td>
                                                    {{ $lead_data->chosen_time }}
                                                </td>
                                            </tr>
                                        @endif
                                    </table>

                                    @if ($lead_data->comments->isNotEmpty())
                                        <div class="mt-4">
                                            <h5 class="mb-3">@lang('translation.leads.comments')</h5>
                                            <div class="list-group">
                                                @foreach ($lead_data->comments->sortByDesc('created_at') as $comment)
                                                    <div class="list-group-item">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <div>
                                                                <i class="mdi mdi-comment-text-outline me-1"></i>
                                                                <small
                                                                    class="text-muted">{{ $comment->user?->name ?? __('translation.unknown') }}</small>
                                                            </div>
                                                            <small
                                                                class="text-muted">{{ dateFormat($comment->created_at, 'd.m.Y H:i') }}</small>
                                                        </div>
                                                        <p class="mt-1 mb-0">{{ $comment->comment }}</p>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    @if ($lead_data->calls->isNotEmpty())
                                        <div class="mt-4">
                                            <h5 class="mb-3">@lang('translation.leads.calls')</h5>
                                            <div class="list-group">
                                                @foreach ($lead_data->calls->sortByDesc('created_at') as $call)
                                                    <div class="list-group-item">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <div>
                                                                <i class="mdi mdi-phone-outline me-1"></i>
                                                                <small
                                                                    class="text-muted">{{ $call->causer->name ?? '-' }}</small>
                                                                @if ($call->status)
                                                                    <span @class([
                                                                        'badge ms-2',
                                                                        'bg-success-subtle text-success' => $call->status === 'answered',
                                                                        'bg-danger-subtle text-danger' => $call->status === 'not_answered',
                                                                        'bg-warning-subtle text-warning' => $call->status === 'busy',
                                                                    ])>
                                                                        @if ($call->status === 'answered')
                                                                            <i class="mdi mdi-phone-check-outline"></i>
                                                                        @elseif($call->status === 'not_answered')
                                                                            <i class="mdi mdi-phone-missed"></i>
                                                                        @elseif($call->status === 'busy')
                                                                            <i class="mdi mdi-phone-alert"></i>
                                                                        @endif
                                                                        {{ __('translation.leads.' . $call->status) }}
                                                                    </span>
                                                                @endif
                                                            </div>
                                                            <small
                                                                class="text-muted">{{ dateFormat($call->created_at, 'd.m.Y H:i') }}</small>
                                                        </div>
                                                        <p class="mt-1 mb-0">{{ $call->comment }}</p>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Calls Card -->
            <div class="card rounded-4">
                <div class="card-header rounded-top-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="mdi mdi-phone me-2"></i>
                            {{ __('translation.calls.calls_history') }}
                        </h5>
                        <span class="badge bg-primary-subtle text-primary">
                            {{ $item->calls->count() }} @lang('translation.calls.total_calls')
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <livewire:student.calls-table :student="$item" />
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="border-bottom card-body">
                    @lang('translation.students.group_log')
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <tr>
                                <th>ID</th>
                                <th>@lang('translation.groups.name')</th>
                                <th>@lang('translation.students.action')</th>
                                <th>@lang('translation.students.causer')</th>
                                <th>@lang('translation.students.date')</th>
                                <th></th>
                            </tr>
                            @forelse($groupLogs as $groupLog)
                                <tr>
                                    <td>{{ $groupLog->id }}</td>
                                    <td class="text-nowrap">
                                        @if ($groupLog->group)
                                            @if ($groupLog->group->trashed())
                                                <span class="text-decoration-line-through text-muted">
                                                    {{ $groupLog->group->name }}
                                                    <span class="badge bg-secondary-subtle text-secondary ms-1">
                                                        @lang('translation.groups.deleted')
                                                    </span>
                                                </span>
                                            @else
                                                <a href="{{ route('admin.groups.show', $groupLog->group_id) }}">
                                                    {{ $groupLog->group->name }}
                                                </a>
                                            @endif
                                        @else
                                            <span class="text-muted">
                                                <del>@lang('translation.groups.not_found')</del>
                                                <span class="badge bg-danger-subtle text-danger ms-1">
                                                    @lang('translation.groups.missing')
                                                </span>
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($groupLog->action == 'attach')
                                            <span class="badge bg-success-subtle text-success">
                                                @lang('translation.students.attached')
                                            </span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger">
                                                @lang('translation.students.detached')
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.users.show', $groupLog->causer_id ?? 0) }}">
                                            {{ $groupLog->causer?->name ?? '-' }}
                                        </a>
                                    </td>
                                    <td>{{ dateFormat($groupLog->created_at, 'd.m.Y H:i') }}</td>
                                    <td></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="100">
                                        <x-no-result />
                                    </td>
                                </tr>
                            @endforelse
                        </table>
                    </div>
                </div>
            </div>

            <livewire:student.student-product-orders-table :student="$item" />

            @if ($sms->count())
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                @lang('translation.students.sent_sms')
                            </div>
                            <div>
                                @lang('translation.students.sent_sms_count') - {{ $sms->count() }}
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <tr>
                                    <th>ID</th>
                                    <th>@lang('translation.sms.content')</th>
                                    <th>@lang('attr.status')</th>
                                    <th>@lang('translation.sms.send_at')</th>
                                    <th>External ID</th>
                                    <th></th>
                                </tr>
                                @forelse($sms as $smsItem)
                                    <tr>
                                        <td>{{ $smsItem->id }}</td>
                                        <td>
                                            {{ $smsItem->content }}
                                        </td>
                                        <td class="text-nowrap">
                                            <span @class([
                                                'badge',
                                                'bg-success-subtle text-success' => $smsItem->status === 'sent',
                                                'bg-warning-subtle text-warning' => $smsItem->status === 'pending',
                                                'bg-danger-subtle text-danger' => $smsItem->status === 'failed',
                                                'bg-info-subtle text-info' => $smsItem->status === 'sending',
                                                'bg-info-subtle text-info' => $smsItem->status === 'waiting',
                                            ])>
                                                @if ($smsItem->status === 'sent')
                                                    <i class="mdi mdi-check"></i>
                                                @elseif($smsItem->status === 'pending')
                                                    <i class="mdi mdi-clock"></i>
                                                @elseif($smsItem->status === 'waiting')
                                                    <i class="mdi mdi-clock"></i>
                                                @elseif($smsItem->status === 'failed')
                                                    <i class="mdi mdi-close"></i>
                                                @elseif($smsItem->status === 'sending')
                                                    <i class="mdi mdi-loading mdi-spin"></i>
                                                @endif
                                                {{ __('translation.sms.' . $smsItem->status) }}
                                            </span>
                                        </td>
                                        <td class="text-nowrap">{{ $smsItem->sent_at }}</td>
                                        <td class="text-nowrap">{{ $smsItem->external_id }}</td>
                                        <td>
                                            <a href="{{ route('admin.sms.show', $smsItem) }}" data-bs-toggle="tooltip"
                                                title="@lang('translation.btn.show')">
                                                <i class="mdi mdi-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="100">
                                            <x-no-result />
                                        </td>
                                    </tr>
                                @endforelse
                            </table>
                        </div>
                        {{ $sms->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Delete confirmation modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('admin.students.destroy', $item->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="deleteModalLabel">
                            @lang('translation.students.delete_confirm')
                        </h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="comment">
                                @lang('translation.students.delete_comment')
                            </label>
                            <textarea class="form-control" id="comment" name="comment" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn bg-danger-subtle text-danger">
                            @lang('translation.btn.delete')
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@push('css')
    <style>
        /* Modern Statistics Cards */
        .stat-card {
            border: none !important;
            border-radius: 20px !important;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08) !important;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1) !important;
            background: #ffffff !important;
            position: relative !important;
            overflow: hidden !important;
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
        }

        .stat-card::before {
            content: '' !important;
            position: absolute !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            bottom: 0 !important;
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.05) 0%, rgba(168, 85, 247, 0.05) 100%) !important;
            opacity: 0 !important;
            transition: opacity 0.4s ease !important;
        }

        .stat-card:hover {
            transform: translateY(-8px) !important;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12) !important;
            border-color: rgba(99, 102, 241, 0.2) !important;
        }

        .stat-card:hover::before {
            opacity: 1 !important;
        }

        .stat-icon {
            width: 42px !important;
            height: 42px !important;
            border-radius: 14px !important;
            background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%) !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            color: white !important;
            font-size: 18px !important;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1) !important;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3) !important;
        }

        .stat-card:hover .stat-icon {
            transform: scale(1.15) rotate(5deg) !important;
            box-shadow: 0 8px 25px rgba(99, 102, 241, 0.4) !important;
        }

        /* Enhanced text styling */
        .stat-card .card-body p {
            color: #64748b !important;
            font-weight: 500;
            letter-spacing: 0.025em;
        }

        .stat-card .card-body h2,
        .stat-card .card-body h3 {
            color: #1e293b !important;
            font-weight: 700;
            letter-spacing: -0.025em;
        }

        /* Add subtle animation to numbers */
        .counter-value {
            display: inline-block;
            transition: all 0.3s ease;
        }

        .stat-card:hover .counter-value {
            transform: scale(1.05);
        }

        /* Different color themes for each card */
        .col-md-4:nth-child(1) .stat-icon {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%) !important;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3) !important;
        }

        .col-md-4:nth-child(1) .stat-card:hover .stat-icon {
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.4) !important;
        }

        .col-md-4:nth-child(2) .stat-icon {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%) !important;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3) !important;
        }

        .col-md-4:nth-child(2) .stat-card:hover .stat-icon {
            box-shadow: 0 8px 25px rgba(239, 68, 68, 0.4) !important;
        }

        .col-md-4:nth-child(3) .stat-icon {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3) !important;
        }

        .col-md-4:nth-child(3) .stat-card:hover .stat-icon {
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4) !important;
        }

        /* Mobile optimizations */
        @media (max-width: 767.98px) {
            .stat-card {
                border-radius: 16px !important;
                box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            }

            .stat-card:hover {
                transform: translateY(-4px);
                box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
            }

            .stat-icon {
                width: 40px !important;
                height: 40px !important;
                font-size: 16px !important;
                border-radius: 12px !important;
            }

            .stat-card:hover .stat-icon {
                transform: scale(1.1) rotate(3deg);
            }
        }

        /* Tablet optimizations */
        @media (min-width: 768px) and (max-width: 1199.98px) {
            .stat-card {
                border-radius: 18px !important;
            }

            .stat-card:hover {
                transform: translateY(-6px);
            }

            .stat-icon {
                width: 44px !important;
                height: 44px !important;
                font-size: 18px !important;
                border-radius: 14px !important;
            }
        }

        /* Add loading animation for counter */
        @keyframes countUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .counter-value {
            opacity: 1;
            animation: countUp 0.6s ease-out forwards;
        }

        /* Subtle pulse effect on hover */
        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.8;
            }
        }

        .stat-card:hover::before {
            animation: pulse 2s infinite;
        }
    </style>
@endpush

@section('script')
    <script src="{{ asset('assets/libs/choices.js/choices.js.min.js') }}"></script>
    <script src="{{ asset('assets/libs/flatpickr/flatpickr.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.3/dist/confetti.browser.min.js"></script>
    <script>
        @if ($errors->any())
            @foreach ($errors->all() as $error)
                window.notifyMessage(`{!! $error !!}`, 'danger')
            @endforeach
        @endif

        @if ($is_today_birthday)
            var count = 200;
            var defaults = {
                origin: {
                    y: 0.7
                }
            };

            function fire(particleRatio, opts) {
                confetti({
                    ...defaults,
                    ...opts,
                    particleCount: Math.floor(count * particleRatio)
                });
            }

            fire(0.25, {
                spread: 26,
                startVelocity: 55,
            });
            fire(0.2, {
                spread: 60,
            });
            fire(0.35, {
                spread: 100,
                decay: 0.91,
                scalar: 0.8
            });
            fire(0.1, {
                spread: 120,
                startVelocity: 25,
                decay: 0.92,
                scalar: 1.2
            });
            fire(0.1, {
                spread: 120,
                startVelocity: 45,
            });
        @endif

        tippy('.t-back-btn', {
            content: "{!! __('translation.btn.back') !!}",
        });
        tippy('.t-more-btn', {
            content: "{!! __('attr.more') !!}",
        });
    </script>
@endsection
