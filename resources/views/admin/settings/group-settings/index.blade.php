@extends('layouts.master')
@section('title')
    @lang('translation.settings')
@endsection
@section('content')
    {{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}
    <div class="row">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="nav flex-column nav-pills text-center" id="v-pills-tab" role="tablist"
                         aria-orientation="vertical">
                        <a class="active nav-link mb-2" id="v-attendance-settings" data-bs-toggle="pill"
                           href="#attendance-settings"
                           role="tab" aria-controls="v-pills-profile" aria-selected="false" tabindex="-1">
                            <span class="mdi mdi-table-cog"></span>
                            @lang("translation.attendance_settings")
                        </a>
                        <a class="nav-link mb-2" id="v-attendance-notification-settings" data-bs-toggle="pill"
                           href="#attendance-notification-settings"
                           role="tab" aria-controls="v-attendance-notification-settings" aria-selected="false"
                           tabindex="-1">
                            <i class="ri-notification-2-line"></i>
                            @lang("translation.attendance_notification_settings")
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-9">
            <div class="tab-content text-muted mt-4 mt-md-0" id="v-pills-tabContent">
                <div class="tab-pane fade active show" id="attendance-settings" role="tabpanel"
                     aria-labelledby="v-attendance-settings">
                    <form action="{{ route('admin.validation-settings.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="card">
                            <div class="card-header">
                                @lang('translation.attendance_settings')
                            </div>
                            <div class="card-body">
                                <livewire:settings.attendance-settings/>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-save"></i>
                                @lang("translation.btn.save")
                            </button>
                        </div>
                    </form>
                </div>
                <div class="tab-pane fade" id="attendance-notification-settings" role="tabpanel"
                     aria-labelledby="v-attendance-notification-settings">
                    <form action="{{ route('admin.settings.group-attendance-update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="card">
                            <div class="card-header">
                                @lang('translation.attendance_notification_settings')
                            </div>
                            <div class="card-body">
                                <div class="form-group mb-3">
                                    <label for="attendance_notice_template">{{__('translation.sms.attendance_notice_template')}}</label>
                                    <select class="form-select select2" id="attendance_notice_template"
                                            name="attendance_notice_template">
                                        <option value="">{{__('translation.choose')}}</option>
                                        @foreach($templates as $template)
                                            <option value="{{ $template->id }}"
                                                    @if($attendanceNoticeTemplate->value == $template->id) selected @endif>
                                                {{ $template->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('attendance_notice_template') <span
                                            class="text-danger">* {{ $message }}</span> @enderror
                                </div>
                                <div class="mb-4">
                                    <div class="form-check form-switch form-switch-md" dir="ltr">
                                        <input type="checkbox" class="form-check-input" id="attendanceSmsStatus"
                                               name="attendance_notice_status"
                                                {{ $attendanceSmsStatus->value == 'enable' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="attendanceSmsStatus" class="text-wrap">
                                            @lang("translation.sms.attendance_notice_sms_status")
                                        </label>
                                        <div class="info text-muted text-sm">
                                            @lang('translation.sms.attendance_notice_sms_status_description')
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button class="btn btn-primary">
                                @lang("translation.btn.save")
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script-bottom')
    <script>
        // When a tab is clicked, store its id in localStorage
        document.querySelectorAll('.nav-link').forEach(function (tab) {
            tab.addEventListener('click', function () {
                localStorage.setItem('selectedAttendanceSettingsTab', this.id);
            });
        });

        // When the page is loaded, retrieve the selected tab from localStorage
        var selectedTabId = localStorage.getItem('selectedAttendanceSettingsTab');
        if (selectedTabId) {
            var selectedTab = document.getElementById(selectedTabId);
            // Trigger a click event on the selected tab
            selectedTab.click();
        }
    </script>
@endsection
