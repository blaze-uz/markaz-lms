@extends('layouts.master')
@section('title')
    @lang('translation.students.payment_notice')
@endsection
@section('content')
    {{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}
    <div class="row d-flex">
        <div class="col-md-6 col-sm-12 d-flex">
            <div class="alert alert-warning w-100" style="border-radius: 16px;">
                <h4 class="alert-heading">@lang('translation.students.payment_notice') ⚠️</h4>
                <p>@lang('translation.students.payment_notice_description')</p>
            </div>
        </div>
        <div class="col-md-6 col-sm-12 d-flex">
            <div class="alert alert-info w-100" style="border-radius: 16px;">
                <h4 class="alert-heading">@lang('translation.students.payment_notice_template') 📝</h4>
                @if ($debtSmsTemplate)
                    <p><strong>@lang('translation.sms.template_name'):</strong> {{ $debtSmsTemplate->name }}</p>
                    <p><strong>@lang('translation.sms.template_content'):</strong></p>
                    <div class="bg-light p-3 rounded mb-2" style="border-radius: 12px;">
                        <small>{{ $debtSmsTemplate->content }}</small>
                    </div>
                    <hr>
                    <p class="mb-0">
                        <a href="{{ route('admin.settings.sms') }}" class="btn btn-outline-info btn-sm"
                            style="border-radius: 8px;"><i class="ri-settings-line me-1"></i> @lang('translation.settings')</a>
                    </p>
                @else
                    <p>{{ $item->course?->name ?? 'Ingliz tili' }} kursidan sizda qarzdorlik mavjud. Kechiktirmasdan
                        to'lashingizni so'raymiz. Hurmat bilan "{{ $studyCenterName }}" o'quv markazi</p>
                @endif
            </div>
        </div>
    </div>

    {{-- SMS Configuration Warnings --}}
    @if (!empty($smsWarnings) || !$canSendSms)
        <div class="row">
            <div class="col-12">
                <div class="alert alert-danger" style="border-radius: 16px;">
                    <h4 class="alert-heading">
                        <i class="ri-error-warning-line me-2"></i>
                        @lang('translation.sms.configuration_required') ⚠️
                    </h4>
                    <p class="mb-3">@lang('translation.sms.cannot_send_sms_description')</p>

                    @if (!empty($smsWarnings))
                        <div class="mb-3">
                            <strong>@lang('translation.sms.missing_configurations'):</strong>
                            <ul class="mb-0 mt-2">
                                @foreach ($smsWarnings as $warning)
                                    <li>{{ $warning }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <hr>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.settings.sms') }}" class="btn btn-primary btn-sm"
                            style="border-radius: 8px;">
                            <i class="ri-settings-line me-1"></i>
                            @lang('translation.sms.configure_sms')
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <form action="{{ route('admin.groups.payment-notice', $item->id) }}" method="POST">
        @csrf

        <div class="card" style="border-radius: 16px;">
            <div class="card-header" style="border-radius: 16px 16px 0 0;">
                <h3 class="card-title">@lang('translation.students.unpaid_students'): {{ count($unpaidStudents) }}</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>@lang('translation.students.full_name')</th>
                                <th>@lang('translation.students.loan')</th>
                                <th>@lang('translation.students.payment_notice')</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($unpaidStudents as $student)
                                <tr>
                                    <td>{{ $student->id }}</td>
                                    <td>
                                        <a href="{{ route('admin.students.show', $student->id) }}"
                                            class="text-reset text-decoration-none">
                                            {{ $student->getFullName() }}
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge bg-danger-subtle text-danger">
                                            {{ money($student->left_amount) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="students[]"
                                                value="{{ $student->id }}" checked />
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if ($unpaidStudents->isEmpty())
                    <div class="text-center py-4">
                        <x-no-result />
                    </div>
                @endif
            </div>
        </div>

        <div class="d-flex justify-content-end mb-3">
            <button class="btn btn-primary"
                @if (!$canSendSms) disabled title="@lang('translation.sms.cannot_send_configuration_required')" @endif
                onclick="return confirm('@lang('translation.students.confirm_send_payment_notice')');" style="border-radius: 12px;">
                <div class="d-flex align-items-center gap-1">
                    @if ($canSendSms)
                        <i class="ri-mail-send-line"></i>
                    @else
                        <i class="ri-error-warning-line"></i>
                    @endif
                    @if ($canSendSms)
                        @lang('translation.students.send')
                    @else
                        @lang('translation.sms.configure_first')
                    @endif
                </div>
            </button>
        </div>

    </form>
@endsection
