@extends('layouts.master')
@section('title')
@lang('translation.settings')
@endsection
@section('css')
<link href="https://unpkg.com/dropzone@6.0.0-beta.1/dist/dropzone.css" rel="stylesheet" type="text/css" />
<style>
    .dropzone {
        min-height: 10px !important;
        padding: 0px;
    }

    .dz-button {
        font-size: 16px !important;
    }
</style>
@endsection
@section('content')
{{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}
<div class="row">
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <div class="text-center nav flex-column nav-pills" id="v-pills-tab" role="tablist"
                    aria-orientation="vertical">
                    <a class="mb-2 nav-link active align-items-baseline" id="v-general-settings" data-bs-toggle="pill"
                        href="#general-settings" role="tab" aria-controls="v-pills-home" aria-selected="false"
                        tabindex="-1">
                        <i class="ri-settings-line"></i> @lang("translation.general-settings")
                    </a>
                    <a class="mb-2 nav-link" id="v-attendance-settings" data-bs-toggle="pill"
                        href="#attendance-settings" role="tab" aria-controls="v-pills-profile" aria-selected="false"
                        tabindex="-1">
                        <span class="mdi mdi-table-cog"></span>
                        @lang("translation.attendance_settings")
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-9">
        <div class="mt-4 tab-content text-muted mt-md-0" id="v-pills-tabContent">
            <div class="tab-pane fade active show" id="general-settings" role="tabpanel"
                aria-labelledby="v-general-settings">
                <div class="card">
                    <div class="card-header">
                        @lang("translation.logo")
                    </div>
                    <div class="card-body">
                        <form action="{{route('admin.settings.update-logo')}}" method="POST"
                            enctype="multipart/form-data" id="logo-form" class="dropzone">
                            @csrf
                        </form>
                    </div>
                </div>
                <form action="{{ route('admin.settings.general-settings-update') }}" enctype="multipart/form-data"
                    method="POST">
                    <div class="d-flex flex-column">
                        <div>
                            @csrf
                            @method('PUT')
                            <div class="card">
                                <div class="card-header">
                                    @lang("translation.settings")
                                </div>
                                <div class="card-body">
                                    <x-input name="name" label="{{ __('translation.site_name') }}"
                                        :value="$settings->name" />
                                    <x-phone-input name="phone" label="{{ __('translation.students.phone') }}"
                                        :value="$settings->phone ?? ''" />
                                    <x-input name="address" label="{{ __('translation.address') }}"
                                        :value="$settings->address ?? ''" />
                                </div>
                            </div>
                        </div>
                        <div>
                            <div class="card">
                                <div class="card-header">
                                    <div class="card-title">
                                        @lang("translation.daily_review_command_run_time")
                                    </div>
                                </div>
                                <div class="card-body">
                                    <label for="time">
                                        @lang("attr.time")
                                    </label>
                                    <input type="time" id="time" name="daily_review_command_run_time"
                                        class="form-control @error('daily_review_command_run_time') is-invalid @enderror mb-3"
                                        value="{{ dateFormat($settings->daily_review_command_run_time, 'H:i') }}">
                                    @error('daily_review_command_run_time')
                                    <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                    <span class="text-sm text-muted">
                                        * @lang("translation.daily_review_command_run_time_description")
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3 d-flex justify-content-end">
                        <button class="btn btn-primary" type="submit">
                            <i class="fas fa-save"></i>
                            @lang("translation.btn.save")
                        </button>
                    </div>
                </form>
            </div>
            <div class="tab-pane fade" id="attendance-settings" role="tabpanel" aria-labelledby="v-attendance-settings">
                <form action="{{ route('admin.validation-settings.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card">
                        <div class="card-header">
                            @lang('translation.attendance_settings')
                        </div>
                        <div class="card-body">
                            <livewire:settings.attendance-settings />
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
        </div>
    </div>
</div>
@endsection
@section('script-bottom')
<script src="https://unpkg.com/dropzone@6.0.0-beta.1/dist/dropzone-min.js"></script>
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

        new Dropzone("#logo-form", {
            thumbnailWidth: 200,
            maxFilesize: 5,
            acceptedFiles: '.jpg, .jpeg, .png',
            maxFiles: 1,
            dictDefaultMessage: "{{__('translation.dropzone.dictDefaultMessage')}}",
        });

</script>
@endsection