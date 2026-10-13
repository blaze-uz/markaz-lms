<div class="card">
    <div class="card-header">@lang("translation.groups.attendance_notice_settings")</div>
    <div class="card-body">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @if($item->groupTimes->isEmpty())
            <div class="alert alert-warning d-flex gap-2 align-items-center">
                <i class="ri-error-warning-line"></i>
                @lang("translation.groups.please_add_group_times_first_to_set_attendance_settings")
            </div>
        @elseif(!$attendanceNoticeStatus)
            <div class="alert alert-warning d-flex gap-2 align-items-center">
                <i class="ri-error-warning-line"></i>
                @lang("translation.groups.attendance_notice_disabled_by_admins")!
            </div>
        @else
            <div class="form-group mb-4">
                <div class="form-check form-switch form-switch-md align-items-baseline" dir="ltr">
                    <input type="checkbox" class="form-check-input" id="customSwitchsizelg"
                           @if($attendanceSettings && $attendanceSettings->auto_send) checked @endif name="auto_send">
                    <label class="form-check-label" for="customSwitchsizelg">
                        @lang("translation.groups.automatically_send_attendance_notice_to_students")
                    </label>
                    @error('automatically_send_attendance_notice_to_students')
                    <div class="text-danger">* {{$message}}</div>
                    @enderror
                </div>
            </div>
            <div id="attendance-settings-form" class="d-none">
                <div class="form-group mb-4">
                    <label for="after_minutes">
                        @lang("translation.groups.send_sms_time_after_lesson_end")
                        (@lang('translation.minutes')):
                    </label>
                    <select class="form-select" id="after_minutes" name="after_minutes">
                        <option value="" selected disabled>
                            @lang("translation.choose")
                        </option>
                        @for($i = 15; $i <= 60; $i += 15)
                            <option value="{{$i}}"
                                    @if($attendanceSettings && $attendanceSettings->after_minutes == $i) selected @endif>
                                {{$i}}
                            </option>
                        @endfor
                    </select>
                    @error('after_minutes')
                    <div class="text-danger">* {{$message}}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="send_sms_for_whom">
                        @lang("translation.groups.send_sms_for_whom")
                    </label>
                    <div class="d-flex justify-content-start gap-2">
                        <div class="mr-3">
                            <input type="radio" class="btn-check" name="send_to" id="student" value="student"
                                   @if($attendanceSettings && $attendanceSettings->send_to == 'student') checked
                                   @endif @if(!$attendanceSettings) checked @endif>
                            <label class="btn btn-outline-primary" for="student">
                                <i class="ri-user-line"></i> {{__("translation.students.student")}}</label>
                        </div>
                        <div>
                            <input type="radio" class="btn-check" name="send_to" id="with_parents"
                                   value="with_parents"
                                   @if($attendanceSettings && $attendanceSettings->send_to == 'with_parents') checked @endif>
                            <label class="btn btn-outline-primary" for="with_parents">
                                <i class="ri-group-line"></i> {{__("translation.groups.with_parents")}}
                            </label>
                        </div>
                        @error('send_to')
                        <div class="text-danger">* {{$message}}</div>
                        @enderror
                    </div>
                </div>
            </div>
        @endif
    </div>
    @push('custom-scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const customSwitchsizelg = document.getElementById('customSwitchsizelg');
                const attendanceSettingsForm = document.getElementById('attendance-settings-form');
                if (customSwitchsizelg.checked) {
                    attendanceSettingsForm.classList.remove('d-none');
                }
                customSwitchsizelg.addEventListener('change', function () {
                    if (this.checked) {
                        attendanceSettingsForm.classList.remove('d-none');
                    } else {
                        attendanceSettingsForm.classList.add('d-none');
                    }
                });
            });
        </script>
    @endpush
</div>