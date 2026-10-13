<div>
    <div class="mb-2 text-muted">
        * @lang('translation.attendance_settings_description')
    </div>
    <input type="hidden" name="group" value="attendance">
    @foreach($attendanceValidationSettings as $validationSetting)
    <div class="mb-2 form-check form-switch">
        <label class="form-check-label" for="{{ $validationSetting->name }}">
            @lang("translation.groups.". $validationSetting->name)
        </label>
        <input class="form-check-input" type="checkbox" role="switch" id="{{ $validationSetting->name }}"
            name="value[{{ $validationSetting->name }}]" {{ $validationSetting->required ? "checked" : "" }}>
            <br>    
        <span class="text-muted">
            @lang("translation.groups.". $validationSetting->name . "_description")
        </span>
    </div>
    @endforeach
</div>