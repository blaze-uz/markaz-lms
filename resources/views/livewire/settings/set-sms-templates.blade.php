<div class="card rounded-4">
    <div class="card-header rounded-top-4">
        @lang('translation.attendance_notification_settings')
    </div>
    <div class="card-body">
        <div class="form-group">
            <label for="attendance_notice_template">{{ __('translation.sms.attendance_notice_template') }}</label>
            <select class="form-select" id="attendance_notice_template" name="attendance_notice_template"
                onchange="showAttendanceTemplateContent(this)">
                <option value="">{{ __('translation.choose') }}</option>
                @foreach ($templates as $template)
                    <option value="{{ $template->id }}" data-content="{{ $template->content }}"
                        @if ($attendanceNoticeTemplate->value == $template->id) selected @endif>
                        {{ $template->name }}
                    </option>
                @endforeach
            </select>
            @error('attendance_notice_template')
                <span class="text-danger">{{ $message }}</span>
            @enderror

            <!-- Template Content Preview -->
            <div id="attendance-template-preview" class="mt-3" style="display: none;">
                <div class="bg-light rounded p-3">
                    <label class="form-label fw-bold text-dark">
                        <i class="fa-solid fa-eye text-primary me-2"></i>
                        {{ __('translation.sms.template_preview') }}
                    </label>
                    <textarea id="attendance-template-content" class="form-control" rows="4" readonly
                        style="background-color: #f8f9fa; color: #495057;"></textarea>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showAttendanceTemplateContent(selectElement) {
            const selectedOption = selectElement.options[selectElement.selectedIndex];
            const previewDiv = document.getElementById('attendance-template-preview');
            const contentDiv = document.getElementById('attendance-template-content');

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
            const selectElement = document.getElementById('attendance_notice_template');
            if (selectElement && selectElement.value) {
                showAttendanceTemplateContent(selectElement);
            }
        });
    </script>
</div>
