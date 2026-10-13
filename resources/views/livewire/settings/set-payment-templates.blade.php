<div>
    <div class="card border-0 shadow rounded-4">
        <div class="card-header text-primary border-0 rounded-top-4">
            <div class="d-flex align-items-center">
                <i class="fa-solid fa-credit-card me-2"></i>
                <h5 class="mb-0">@lang('translation.sms.payment_template_settings')</h5>
            </div>
        </div>
        <div class="card-body bg-light">
            <!-- Payment Template Selection Section -->
            <div class="form-group mb-4">
                <div class="bg-white rounded p-4 shadow-sm border-start border-4 border-primary">
                    <label for="payment_sms_template" class="form-label fw-bold text-dark">
                        @lang('translation.sms.payment_sms_template')
                    </label>
                    <select class="form-select" id="payment_sms_template" name="payment_sms_template"
                        wire:model="payment_sms_template" onchange="showTemplateContent(this)">
                        <option value="">@lang('translation.sms.select_template')</option>
                        @foreach ($templates as $template)
                            <option value="{{ $template->id }}" data-content="{{ $template->content }}"
                                {{ $paymentSmsTemplate && $paymentSmsTemplate->value == $template->id ? 'selected' : '' }}>
                                {{ $template->name }}
                            </option>
                        @endforeach
                    </select>
                    <div class="form-text">
                        {{ __('translation.sms.payment_sms_template_description') }}
                    </div>
                    @error('payment_sms_template')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Template Content Preview -->
            <div id="template-preview" class="mb-4" style="display: none;">
                <div class="bg-white rounded p-4 shadow-sm border-start border-4 border-success">
                    <label class="form-label fw-bold text-dark">
                        <i class="fa-solid fa-eye text-success me-2"></i>
                        {{ __('translation.sms.template_preview') }}
                    </label>
                    <textarea id="template-content" class="form-control" rows="4" readonly
                        style="background-color: #f8f9fa; color: #495057;"></textarea>
                    <small class="form-text text-muted mt-2 d-block">
                        @lang('translation.sms.template_variables_info')
                    </small>
                </div>
            </div>

            <!-- Payment Notifications Section -->
            <div class="form-group mb-4">
                <div class="bg-white rounded p-4 shadow-sm border-start border-4 border-warning">
                    <label class="form-label fw-bold text-dark">
                        <i class="fa-solid fa-comment-dots text-warning me-2"></i>
                        @lang('translation.sms.payment_notifications')
                    </label>
                    <div class="d-flex justify-content-between align-items-center bg-light rounded p-3">
                        <span class="fw-semibold text-dark">@lang('translation.sms.sms_sending_status')</span>
                        <div class="form-check form-switch" dir="ltr">
                            <input type="checkbox" class="form-check-input" id="payment_sms_status"
                                name="payment_sms_status" value="1"
                                {{ $paymentSmsStatus && $paymentSmsStatus->value == '1' ? 'checked' : '' }}>
                            <label class="form-check-label" for="payment_sms_status"></label>
                        </div>
                    </div>
                    <small class="form-text text-muted mt-2 d-block">
                        @lang('translation.sms.sms_sending_status_description')
                    </small>
                </div>
            </div>

            <!-- Payment SMS Information Section -->
            <div class="form-group mb-4">
                <div class="bg-white rounded p-4 shadow-sm border-start border-4 border-info">
                    <label class="form-label fw-bold text-dark">
                        <i class="fa-solid fa-circle-info text-info me-2"></i>
                        @lang('translation.sms.payment_sms_information')
                    </label>
                    <div class="alert alert-info border-0 mb-0">
                        <i class="fa-solid fa-lightbulb me-2"></i>
                        @lang('translation.sms.payment_sms_info_text')
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showTemplateContent(selectElement) {
            const selectedOption = selectElement.options[selectElement.selectedIndex];
            const previewDiv = document.getElementById('template-preview');
            const contentDiv = document.getElementById('template-content');

            if (selectedOption.value && selectedOption.dataset.content) {
                contentDiv.value = selectedOption.dataset.content;
                previewDiv.style.display = 'block';

                // Add animation
                previewDiv.style.opacity = '0';
                previewDiv.style.transform = 'translateY(20px)';
                previewDiv.style.transition = 'all 0.3s ease';

                setTimeout(() => {
                    previewDiv.style.opacity = '1';
                    previewDiv.style.transform = 'translateY(0)';
                }, 100);
            } else {
                previewDiv.style.display = 'none';
                contentDiv.value = '';
            }
        }

        // Show template content on page load if template is already selected
        document.addEventListener('DOMContentLoaded', function() {
            const selectElement = document.getElementById('payment_sms_template');
            if (selectElement && selectElement.value) {
                showTemplateContent(selectElement);
            }
        });
    </script>
</div>
