@extends('layouts.master')
@section('title')
    @lang('translation.sms.settings')
@endsection
@section('content')
    {{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}
    <div class="row">
        <div class="col-md-3">
            <div class="card rounded-4">
                <div class="card-body">
                    <div class="nav flex-column nav-pills text-center" id="v-pills-tab" role="tablist"
                        aria-orientation="vertical">
                        <a class="nav-link active mb-2 align-items-baseline" id="v-sms-settings" data-bs-toggle="pill"
                            href="#sms-settings" role="tab" aria-controls="v-pills-home" aria-selected="false"
                            tabindex="-1">
                            <i class="ri-settings-line"></i> @lang('translation.sms.settings')
                        </a>
                        <a class="nav-link mb-2" id="v-sms-templates" data-bs-toggle="pill" href="#sms-templates"
                            role="tab" aria-controls="v-pills-profile" aria-selected="false" tabindex="-1">
                            <i class="fa-regular fa-envelope"></i>
                            @lang('translation.sms.templates')
                        </a>
                        <a class="nav-link mb-2" id="v-set-sms-templates" data-bs-toggle="pill" href="#set-sms-templates"
                            role="tab" aria-controls="v-set-sms-templates" aria-selected="false" tabindex="-1">
                            <i class="fa-solid fa-gears"></i>
                            @lang('translation.sms.attendance_templates')
                        </a>
                        <a class="nav-link mb-2" id="v-set-payment-templates" data-bs-toggle="pill"
                            href="#set-payment-templates" role="tab" aria-controls="v-set-payment-templates"
                            aria-selected="false" tabindex="-1">
                            <i class="fa-solid fa-credit-card"></i>
                            @lang('translation.sms.payment_templates')
                        </a>
                        <a class="nav-link mb-2" id="v-set-debt-templates" data-bs-toggle="pill" href="#set-debt-templates"
                            role="tab" aria-controls="v-set-debt-templates" aria-selected="false" tabindex="-1">
                            <i class="fa-solid fa-exclamation-triangle"></i>
                            @lang('translation.sms.debt_template_settings')
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-9">
            <div class="tab-content text-muted mt-4 mt-md-0" id="v-pills-tabContent">
                <div class="tab-pane fade active show" id="sms-settings" role="tabpanel" aria-labelledby="v-sms-settings">
                    <livewire:settings.sms-settings-table />
                </div>
                <div class="tab-pane fade" id="sms-templates" role="tabpanel" aria-labelledby="v-sms-templates">
                    <livewire:settings.sms-templates-table lazy />
                </div>
                <div class="tab-pane fade" id="set-sms-templates" role="tabpanel" aria-labelledby="v-set-sms-templates">
                    <form action="{{ route('admin.settings.set-template') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <livewire:settings.set-sms-templates />
                        <div class="d-flex justify-content-end">
                            <button class="btn btn-primary">
                                @lang('translation.btn.save')
                            </button>
                        </div>
                    </form>
                </div>
                <div class="tab-pane fade" id="set-payment-templates" role="tabpanel"
                    aria-labelledby="v-set-payment-templates">
                    <form action="{{ route('admin.settings.set-payment-template') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <livewire:settings.set-payment-templates />
                        <div class="d-flex justify-content-end mb-3">
                            <button class="btn btn-primary">
                                @lang('translation.btn.save')
                            </button>
                        </div>
                    </form>
                </div>
                <div class="tab-pane fade" id="set-debt-templates" role="tabpanel" aria-labelledby="v-set-debt-templates">
                    <form action="{{ route('admin.settings.set-debt-template') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <livewire:settings.set-debt-templates lazy />
                        <div class="d-flex justify-content-end mb-3">
                            <button class="btn btn-primary">
                                @lang('translation.btn.save')
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Moderation Confirmation Modal -->
    <div class="modal fade" id="moderationModal" tabindex="-1" aria-labelledby="moderationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4">
                <div class="modal-header rounded-top-4">
                    <h5 class="modal-title" id="moderationModalLabel">@lang('translation.sms.moderation_confirmation_title')</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>@lang('translation.sms.moderation_confirmation_message')</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">
                        @lang('translation.sms.cancel')
                    </button>
                    <form id="moderationForm" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn btn-primary rounded-3">
                            @lang('translation.sms.confirm_send_to_moderation')
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- SMS Moderation Instructions Modal -->
    <div class="modal fade" id="instructionModal" tabindex="-1" aria-labelledby="instructionModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content rounded-4">
                <div class="modal-header rounded-top-4">
                    <h5 class="modal-title" id="instructionModalLabel">
                        <i class="fas fa-info-circle text-primary me-2"></i>
                        @lang('translation.sms_moderation_instructions')
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Why Moderation is Needed -->
                    <div class="mb-4">
                        <h6 class="fw-bold text-primary mb-3">
                            <i class="fas fa-question-circle me-2"></i>
                            @lang('translation.sms_moderation_why_needed')
                        </h6>
                        <p class="mb-3">@lang('translation.sms_moderation_why_text')</p>
                        <ul class="list-unstyled">
                            @foreach (__('translation.sms_moderation_reasons') as $reason)
                                <li class="mb-2">
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    {{ $reason }}
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <!-- How Moderation Works -->
                    <div class="mb-4">
                        <h6 class="fw-bold text-primary mb-3">
                            <i class="fas fa-cogs me-2"></i>
                            @lang('translation.sms_moderation_how_works')
                        </h6>
                        <div class="row g-3">
                            @foreach (__('translation.sms_moderation_process') as $key => $step)
                                <div class="col-md-6">
                                    <div class="card border-0 bg-light h-100">
                                        <div class="card-body p-3">
                                            <h6 class="card-title text-primary fw-bold mb-2">{{ $step['title'] }}</h6>
                                            <p class="card-text small mb-0">{{ $step['description'] }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Moderation Timing -->
                    <div class="mb-4">
                        <h6 class="fw-bold text-primary mb-3">
                            <i class="fas fa-clock me-2"></i>
                            @lang('translation.sms_moderation_timing')
                        </h6>
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            @lang('translation.sms_moderation_timing_text')
                        </div>
                    </div>

                    <!-- Template Statuses -->
                    <div class="mb-4">
                        <h6 class="fw-bold text-primary mb-3">
                            <i class="fas fa-list me-2"></i>
                            @lang('translation.sms_moderation_statuses')
                        </h6>
                        <p class="mb-3">@lang('translation.sms_moderation_statuses_text')</p>
                        <div class="row g-2">
                            @foreach (__('translation.sms_moderation_status_list') as $status => $description)
                                <div class="col-md-6">
                                    <div class="d-flex align-items-start">
                                        <span
                                            class="badge
                                            @if ($status === 'not_registered') bg-secondary
                                            @elseif($status === 'moderation') bg-warning
                                            @elseif($status === 'registered') bg-success
                                            @else bg-danger @endif
                                            me-2 mt-1">{{ __('translation.sms.status_' . $status) }}</span>
                                        <small class="text-muted">{{ $description }}</small>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Important Notes -->
                    <div class="mb-0">
                        <h6 class="fw-bold text-warning mb-3">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            @lang('translation.sms_moderation_important')
                        </h6>
                        <div class="alert alert-warning mb-0">
                            <ul class="mb-0 ps-3">
                                @foreach (__('translation.sms_moderation_important_notes') as $note)
                                    <li class="mb-1">{{ $note }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>
                        @lang('translation.close')
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script-bottom')
    <script>
        // When a tab is clicked, store its id in localStorage
        document.querySelectorAll('.nav-link').forEach(function(tab) {
            tab.addEventListener('click', function() {
                localStorage.setItem('selectedSettingsTab', this.id);
            });
        });

        // When the page is loaded, retrieve the selected tab from localStorage
        var selectedTabId = localStorage.getItem('selectedSettingsTab');
        if (selectedTabId) {
            var selectedTab = document.getElementById(selectedTabId);
            // Trigger a click event on the selected tab
            selectedTab.click();
        }

        // Moderation confirmation modal script
        document.addEventListener('DOMContentLoaded', function() {
            // Event delegation for moderation confirmation buttons
            document.addEventListener('click', function(e) {
                if (e.target.closest('[data-action="moderation-confirmation"]')) {
                    e.preventDefault();
                    const button = e.target.closest('[data-action="moderation-confirmation"]');
                    const templateId = button.getAttribute('data-template-id');

                    const form = document.getElementById('moderationForm');
                    form.action = `/admin/sms-templates/${templateId}/send-to-moderation`;

                    const modal = new bootstrap.Modal(document.getElementById('moderationModal'));
                    modal.show();
                }
            });
        });
    </script>
@endsection
