<div>
    <div class="card rounded-4">
        <div class="card-header rounded-top-4">
            @lang('translation.sms.settings')
        </div>
        <div class="card-body">
            <div class="mb-4">
                <div class="form-check form-switch form-switch-md" dir="ltr">
                    <input type="checkbox" class="form-check-input" id="customSwitchsizelg" name="send_sms"
                        wire:change="changeStatus" {{ $smsStatus == 'enable' ? 'checked' : '' }}
                        aria-labelledby="send-sms-label" aria-describedby="send-sms-description">
                    <label class="form-check-label" for="customSwitchsizelg" id="send-sms-label">
                        @lang('translation.sms.send_status')
                    </label>
                    <div class="text-sm info text-muted">
                        @lang('translation.sms.send_status_description')
                    </div>
                    <div id="send-sms-description" class="visually-hidden">
                        @lang('translation.sms.send_status_description')
                    </div>
                </div>
            </div>
            <div class="mb-4">
                <div class="form-check form-switch form-switch-md" dir="ltr">
                    <input type="checkbox" class="form-check-input" id="paymentSmsStatus" name="payment_sms_status"
                        {{ $paymentSmsStatus == 'enable' ? 'checked' : '' }} wire:change="changePaymentStatus">
                    <label class="form-check-label" for="paymentSmsStatus" class="text-wrap">
                        @lang('translation.sms.payment_sms_status')
                    </label>
                    <div class="text-sm info text-muted">
                        @lang('translation.sms.payment_sms_status_description')
                    </div>
                </div>
            </div>
            <div class="mb-4">
                <div class="form-check form-switch form-switch-md" dir="ltr">
                    <input type="checkbox" class="form-check-input" id="attendanceSmsStatus" name="payment_sms_status"
                        {{ $attendanceSmsStatus == 'enable' ? 'checked' : '' }} wire:change="changeAttendanceStatus">
                    <label class="form-check-label" for="attendanceSmsStatus" class="text-wrap">
                        @lang('translation.sms.attendance_notice_sms_status')
                    </label>
                    <div class="text-sm info text-muted">
                        @lang('translation.sms.attendance_notice_sms_status_description')
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
