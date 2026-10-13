<div>
    <!-- Hidden inputs to submit all current values with the form -->
    <input type="hidden" name="selectedTemplateId" value="{{ $selectedTemplateId }}">
    <input type="hidden" name="autoSendEnabled" value="{{ $autoSendEnabled ? '1' : '0' }}">
    <input type="hidden" name="sendDay" value="{{ $sendDay }}">
    <input type="hidden" name="sendTime" value="{{ $sendTime }}">
    <input type="hidden" name="sendType" value="{{ $sendType }}">
    @if ($selectedGroups && is_array($selectedGroups))
        @foreach ($selectedGroups as $groupId)
            <input type="hidden" name="selectedGroups[]" value="{{ $groupId }}">
        @endforeach
    @endif

    <div class="card border-0 shadow rounded-4">
        <div class="card-header text-primary border-0 rounded-top-4">
            <div class="d-flex align-items-center">
                <i class="fa-solid fa-exclamation-triangle me-2"></i>
                <h5 class="mb-0">@lang('translation.sms.debt_template_settings')</h5>
            </div>
        </div>
        <div class="card-body bg-light">
            <!-- Debt Template Selection Section -->
            <div class="form-group mb-3">
                <div class="bg-white rounded p-3 shadow-sm border-start border-4 border-danger">
                    <label for="debt_sms_template" class="form-label fw-bold text-dark mb-2">
                        @lang('translation.sms.debt_sms_template')
                    </label>
                    <select class="form-select mb-2" id="debt_sms_template" name="debt_sms_template"
                        wire:model.live="selectedTemplateId">
                        <option value="">@lang('translation.sms.select_template')</option>
                        @foreach ($templates as $template)
                            <option value="{{ $template->id }}">
                                {{ $template->name }}
                            </option>
                        @endforeach
                    </select>
                    <div class="form-text text-muted small">
                        {{ __('translation.sms.debt_sms_template_description') }}
                    </div>
                    @error('debt_sms_template')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Template Content Preview -->
            @if ($selectedTemplateContent)
                <div class="mb-3">
                    <div class="bg-white rounded p-3 shadow-sm border-start border-4 border-success">
                        <label class="form-label fw-bold text-dark mb-2">
                            <i class="fa-solid fa-eye text-success me-2"></i>
                            {{ __('translation.sms.template_preview') }}
                        </label>
                        <div class="form-control mb-2"
                            style="background-color: #f8f9fa; color: #495057; min-height: 80px; border: 1px solid #dee2e6; padding: 12px;">
                            {{ $selectedTemplateContent }}
                        </div>
                        <small class="form-text text-muted small">
                            <i class="fa-solid fa-info-circle me-1"></i>
                            @lang('translation.sms.template_variables_info')
                        </small>
                    </div>
                </div>
            @endif

            <!-- Auto Send SMS Section -->
            <div class="form-group mb-3">
                <div class="bg-white rounded p-3 shadow-sm border-start border-4 border-primary">
                    <label class="form-label fw-bold text-dark mb-2">
                        <i class="fa-solid fa-paper-plane text-primary me-2"></i>
                        @lang('translation.sms.auto_send_sms')
                    </label>
                    <div class="d-flex justify-content-between align-items-center bg-light rounded p-2 mb-2">
                        <span class="fw-semibold text-dark">@lang('translation.sms.auto_send_sms')</span>
                        <div class="form-check form-switch" dir="ltr">
                            <input type="checkbox" class="form-check-input" id="auto_send_sms" name="auto_send_sms"
                                wire:model.live="autoSendEnabled">
                            <label class="form-check-label" for="auto_send_sms"></label>
                        </div>
                    </div>
                    @if ($autoSendEnabled)
                        <div class="mt-3 p-3 bg-light rounded">
                            <!-- Send Type Selection -->
                            <div class="row mb-3">
                                <div class="col-12">
                                    <label for="send_type" class="form-label fw-semibold text-dark">
                                        <i class="fa-solid fa-users me-1"></i>
                                        @lang('translation.sms.send_type')
                                    </label>
                                    <select class="form-select" id="send_type" name="send_type"
                                        wire:model.live="sendType" wire:loading.attr="disabled" wire:target="sendType">
                                        <option value="all">@lang('translation.sms.send_to_all')</option>
                                        <option value="custom">@lang('translation.sms.send_to_custom')</option>
                                    </select>
                                    <div wire:loading wire:target="sendType" class="mt-2">
                                        <div class="d-flex align-items-center">
                                            <div class="spinner-border spinner-border-sm text-primary me-2"
                                                role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                            <small class="text-muted">@lang('translation.sms.updating_send_type')</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Groups Selection (shown when Custom is selected) -->
                            @if ($sendType === 'custom')
                                <div class="row mb-3">
                                    <div class="col-12">
                                        <label class="form-label fw-semibold text-dark">
                                            <i class="fa-solid fa-list-check me-1"></i>
                                            @lang('translation.sms.select_groups')
                                        </label>
                                        <div class="bg-white rounded p-3 border"
                                            style="max-height: 200px; overflow-y: auto;" wire:loading.class="opacity-50"
                                            wire:target="toggleGroup">
                                            @foreach ($groups as $group)
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox"
                                                        id="group_{{ $group->id }}"
                                                        wire:click="toggleGroup({{ $group->id }})"
                                                        wire:loading.attr="disabled"
                                                        wire:target="toggleGroup({{ $group->id }})"
                                                        @if (is_array($selectedGroups) && in_array($group->id, $selectedGroups)) checked @endif>
                                                    <label class="form-check-label" for="group_{{ $group->id }}">
                                                        {{ $group->name }}
                                                        <small class="text-muted">({{ $group->students_count }}
                                                            @lang('translation.students.students'))</small>
                                                        <span wire:loading
                                                            wire:target="toggleGroup({{ $group->id }})"
                                                            class="ms-2">
                                                            <i class="fa-solid fa-spinner fa-spin text-primary"></i>
                                                        </span>
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- Day and Time Selection -->
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="send_day" class="form-label fw-semibold text-dark">
                                        <i class="fa-solid fa-calendar-day me-1"></i>
                                        @lang('translation.sms.send_day')
                                    </label>
                                    <select class="form-select" id="send_day" name="send_day"
                                        wire:model.live="sendDay">
                                        <option value="">@lang('translation.sms.select_day')</option>
                                        @for ($i = 1; $i <= 31; $i++)
                                            <option value="{{ $i }}">{{ $i }}@lang('translation.sms.day_suffix')
                                            </option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="send_time" class="form-label fw-semibold text-dark">
                                        <i class="fa-solid fa-clock me-1"></i>
                                        @lang('translation.sms.send_time')
                                    </label>
                                    <input type="time" class="form-control" id="send_time" name="send_time"
                                        wire:model.live="sendTime">
                                </div>
                            </div>
                            <div class="mt-2">
                                <small class="text-muted">
                                    <i class="fa-solid fa-info-circle me-1"></i>
                                    @lang('translation.sms.schedule_sms_description')
                                </small>
                            </div>
                        </div>
                    @endif

                    @if ($lastSentAt)
                        <div class="alert alert-success border-0 p-2 mb-0 mt-2">
                            <i class="fa-solid fa-check-circle me-2"></i>
                            @lang('translation.sms.last_sent_at'): {{ $lastSentAt }}
                        </div>
                    @endif
                    <small class="form-text text-muted small">
                        @lang('translation.sms.auto_send_sms_description')
                    </small>
                </div>
            </div>

            <!-- Debt SMS Information Section -->
            <div class="form-group mb-3">
                <div class="bg-white rounded p-3 shadow-sm border-start border-4 border-info">
                    <label class="form-label fw-bold text-dark mb-2">
                        <i class="fa-solid fa-circle-info text-info me-2"></i>
                        @lang('translation.sms.debt_sms_information')
                    </label>
                    <div class="alert alert-info border-0 mb-0 p-2">
                        <i class="fa-solid fa-lightbulb me-2"></i>
                        @lang('translation.sms.debt_sms_info_text')
                    </div>
                </div>
            </div>

            <!-- Hidden input for form submission -->
            <input type="hidden" name="selectedTemplateId" value="{{ $selectedTemplateId }}">
        </div>
    </div>

</div>
