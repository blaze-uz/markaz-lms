<div>
    @push('css')
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
    @endpush
    <div class="card">
        <div class="card-header">
            @lang("translation.telegram.daily_report_settings")
        </div>
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
            <div class="form-group mb-4">
                <div class="form-check form-switch form-switch-md align-items-baseline" dir="ltr">
                    <input type="checkbox" class="form-check-input" id="customSwitchsizelg"
                           @if($telegramDailyReportStatus) checked @endif name="status">
                    <label class="form-check-label" for="customSwitchsizelg">
                        @lang("translation.telegram.daily_report")
                    </label>
                    @error('telegram_daily_report')
                    <div class="text-danger">* {{$message}}</div>
                    @enderror
                </div>
            </div>
            <div id="telegram-daily-report-form" class="d-none">
                <div class="form-group mb-4">
                    <label for="time">
                        @lang("translation.telegram.report_send_time")
                    </label>
                    <input type="time" class="form-control" id="time" name="time"
                           value="{{ $telegramDailyReportTime }}">
                    @error('time')
                    <div class="text-danger">* {{$message}}</div>
                    @enderror
                </div>
                <div class="form-group mb-4">
                    <label for="selected_telegram_ids">
                        @lang("translation.telegram.select_telegram_ids")
                    </label>
                    <select class="form-select select2" multiple id="selected_telegram_ids" name="telegram_ids[]">
                        @foreach($telegrams as $telegram)
                            <option value="{{ $telegram->id }}">
                                {{ $telegram->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('selected_telegram_ids')
                    <div class="text-danger">* {{$message}}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>
    @push('custom-scripts')
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const customSwitchsizelg = document.getElementById('customSwitchsizelg');
                const attendanceSettingsForm = document.getElementById('telegram-daily-report-form');
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
            $(document).ready(function () {
                $('.select2').select2({
                    allowClear: true,
                    multiple: true
                });
            });
        </script>
    @endpush
</div>
