<div class="card">
    <div class="card-header">
        <div class="card-title">@lang('translation.sms.hint')</div>
    </div>
    <div class="card-body">
        <p class="card-text text-muted">
            {{ __('translation.sms.hint_description') }}
        </p>
        <h5>
            @lang('translation.sms.available_variables')
        </h5>
        <div class="px-2 text-lg">
            <span class="fw-semibold">{STUDY_CENTER}</span> - <span class="text-muted">
                {{ __('translation.sms.available_variables_study_center') }}
            </span><br>
            <span class="fw-semibold">{STUDENT}</span> - <span class="text-muted">
                {{ __('translation.sms.available_variables_student') }}
            </span><br>
            <span class="fw-semibold">{TEACHER}</span> - <span class="text-muted">
                {{ __('translation.sms.available_variables_teacher') }}
            </span><br>
            {{-- <span class="fw-semibold">{COURSE}</span> - <span class="text-muted">kurs nomi</span><br> --}}
            <span class="fw-semibold">{PHONE}</span> - <span class="text-muted">
                {{ __('translation.sms.available_variables_phone') }}
            </span><br>
        </div>

        <div class="mt-4">
            <h5 class="text-warning">
                <i class="bx bx-warning me-2"></i>@lang('translation.sms.payment_variables')
            </h5>
            <div class="px-2 text-lg">
                <span class="fw-semibold">{AMOUNT}</span> - <span class="text-muted">
                    {{ __('translation.sms.available_variables_amount') }}
                </span><br>
                <span class="fw-semibold">{DATE}</span> - <span class="text-muted">
                    {{ __('translation.sms.available_variables_payment_date') }}
                </span><br>
            </div>
            <div class="mt-2 alert alert-warning">
                <i class="bx bx-info-circle me-2"></i>
                {{ __('translation.sms.payment_variables_warning') }}
            </div>
        </div>

        <div class="mt-3">
            <h5>
                @lang('translation.sms.sms_length')
            </h5>
            <div class="px-2 text-lg">
                <span class="fw-semibold">0-120</span>
                {{ __('translation.sms.sms_count_120') }}
                <br>
                <span class="fw-semibold">121-240</span>
                {{ __('translation.sms.sms_count_240') }}
            </div>
            <div class="mt-4 alert alert-warning">
                {{ __('translation.sms.sms_count_warning') }}
            </div>
        </div>
    </div>
</div>
