@extends('layouts.master')
@section('title')
@lang("translation.groups.settings")
@stop
@section('content')
{{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}
<div class="row">
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <div class="nav flex-column nav-pills text-center" id="v-pills-tab" role="tablist"
                    aria-orientation="vertical">
                    <a class="nav-link active mb-2" id="v-groups-settings" data-bs-toggle="pill" href="#settings"
                        role="tab" aria-controls="v-pills-home" aria-selected="true" tabindex="-1">
                        <i class="ri-settings-line"></i> @lang("translation.groups.settings")
                    </a>
                    <a class="nav-link mb-2" id="v-groups-attendance-notice-settings" data-bs-toggle="pill"
                        href="#attendance-notice-settings" role="tab" aria-controls="v-pills-profile"
                        aria-selected="false" tabindex="-1">
                        <i class="ri-time-line"></i> @lang("translation.groups.attendance_notice_settings")
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-9">
        <div class="tab-content text-muted mt-4 mt-md-0" id="v-pills-tabContent">
            <div class="tab-pane fade active show" id="settings" role="tabpanel" aria-labelledby="v-pills-home-tab">
                <form action="{{ route('admin.groups.update', $item->id) }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    @method("PUT")
                    @include("admin.groups.partials.form")
                    <div class="hstack gap-2 justify-content-end mb-5">
                        <x-button id="detach-btn" class="btn-success">
                            @lang("translation.btn.save")
                        </x-button>
                        <a href="{{ route('admin.groups.index') }}" type="button"
                            class="btn btn-warning">@lang("translation.btn.back")</a>
                    </div>
                </form>
            </div>
            <div class="tab-pane fade" id="attendance-notice-settings" role="tabpanel"
                aria-labelledby="v-pills-profile-tab">
                <form action="{{ route('admin.groups.attendance-settings', $item->id) }}" method="POST">
                    @csrf
                    @method("PUT")
                    @include("admin.groups.partials.attendance-settings")
                    <div class="hstack gap-2 justify-content-end mb-5">
                        <button class="btn btn-success">
                            @lang("translation.btn.save")
                        </button>
                        <a href="{{ route('admin.groups.index') }}" type="button" class="btn btn-warning">
                            @lang("translation.btn.back")
                        </a>
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
                localStorage.setItem('selectedTab', this.id);
            });
        });

        // When the page is loaded, retrieve the selected tab from localStorage
        var selectedTabId = localStorage.getItem('selectedTab');
        if (selectedTabId) {
            var selectedTab = document.getElementById(selectedTabId);
            // Trigger a click event on the selected tab
            selectedTab.click();
        }
</script>
@endsection
