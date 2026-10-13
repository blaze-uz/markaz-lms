<div class="row">
    <div class="col-sm-12 col-md-2"></div>
    <div class="col-sm-12 col-md-8">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">@lang('translation.staff_settings')</h4>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <div class="form-check form-switch form-switch-md" dir="ltr">
                        <input type="checkbox" class="cursor-pointer form-check-input" id="customSwitchsizelg"
                            name="send_sms" wire:change="changeStatus" {{ $auto_payment=='enable' ? 'checked' : '' }}
                            aria-labelledby="send-sms-label" aria-describedby="send-sms-description">
                        <label class="cursor-pointer form-check-label" for="customSwitchsizelg" id="send-sms-label">
                            @lang("translation.auto_payment_for_staff")
                        </label>
                        <div class="text-sm cursor-pointer info text-muted">
                            @lang('translation.auto_payment_for_staff_description')
                        </div>
                        <div id="send-sms-description" class="visually-hidden">
                            @lang("translation.auto_payment_for_staff_description")
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-12 col-md-2"></div>
    @push('custom-scripts')
    @script
    <script>
        $wire.on('settings-updated', () => {
            window.notifyMessage("{!! __('translation.alert_messages.updated') !!}", "success");
        });
    </script>
    @endscript
    @endpush
</div>