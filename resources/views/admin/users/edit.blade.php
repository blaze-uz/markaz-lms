@extends('layouts.master')
@section('title')
    @lang("translation.users.edit")
@stop
@section('content')
    {{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}
    <div class="row">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="nav flex-column nav-pills text-center" id="v-pills-tab" role="tablist"
                         aria-orientation="vertical">
                        <a class="nav-link active mb-2 align-items-baseline" id="v-user-settings"
                           data-bs-toggle="pill"
                           href="#user-settings"
                           role="tab" aria-controls="v-pills-home" aria-selected="false" tabindex="-1">
                            <i class="ri-settings-line"></i> @lang("translation.users.settings")
                        </a>
                        <a class="nav-link mb-2" id="v-financial-settings" data-bs-toggle="pill"
                           href="#financial-settings"
                           role="tab" aria-controls="v-pills-profile" aria-selected="false" tabindex="-1">
                            <span class="mdi mdi-table-cog"></span>
                            @lang("translation.users.financial_settings")
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-9">
            <div class="tab-content text-muted mt-4 mt-md-0" id="v-pills-tabContent">
                <div class="tab-pane fade active show" id="user-settings" role="tabpanel"
                     aria-labelledby="v-user-settings">
                    <form action="{{ route('admin.users.update', $item->id) }}" method="POST"
                          enctype="multipart/form-data">
                        @csrf
                        @method("PUT")
                        <div class="card">
                            @include("admin.users.partials.form")
                        </div>
                        <div class="hstack gap-2 justify-content-end mb-3">
                            <x-button id="detach-btn" class="btn-success">
                                @lang("translation.btn.save")
                            </x-button>
                            <a href="{{ route("admin.users.index") }}" type="button"
                               class="btn btn-warning">@lang("translation.btn.back")</a>
                        </div>
                    </form>
                </div>
                <div class="tab-pane fade" id="financial-settings" role="tabpanel"
                     aria-labelledby="v-financial-settings">
                    <form action="{{ route('admin.users.contracts.update', $item->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="card">
                            <div class="card-header">
                                @lang("translation.users.financial_settings")
                            </div>
                            <div class="card-body">
                                <div class="form-check form-switch form-switch-md" dir="ltr">
                                    <input type="checkbox" class="form-check-input" id="customSwitchsizelg"
                                           name="auto_salary_status"
                                           @checked($user_contract ?? false)
                                           aria-labelledby="salary-label" aria-describedby="salary-description">
                                    <label class="form-check-label" for="customSwitchsizelg" id="salary-label">
                                        @lang("translation.users.make_auto_salary_payment")
                                    </label>
                                    <div class="info text-muted text-sm">
                                        @lang("translation.users.make_auto_salary_payment_hint")
                                    </div>
                                </div>
                                <div id="auto-salary-form" class="d-none">
                                    <div class="form-group">
                                        <x-money-input :label="__('translation.users.amount')" name="amount" id="amount"
                                                       :value="$user_contract->amount ?? ''"/>
                                    </div>
                                    <div class="form-group">
                                        <label for="payment_day">
                                            @lang("translation.users.payment_day")
                                        </label>
                                        <input type="number" id="payment_day" name="payment_day"
                                               @class(['form-control', 'is-invalid' => $errors->has('payment_day')])
                                               value="{{ $user_contract?->payment_day }}">
                                        @error('payment_day')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @else
                                            <span class="text-muted">
                                                * @lang("translation.users.payment_day_hint")
                                            </span>
                                            @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end my-3">
                            <button type="submit" class="btn btn-success">
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
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const customSwitchsizelg = document.getElementById('customSwitchsizelg');
            const attendanceSettingsForm = document.getElementById('auto-salary-form');
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

            // When a tab is clicked, store its id in localStorage
            document.querySelectorAll('.nav-link').forEach(function (tab) {
                tab.addEventListener('click', function () {
                    localStorage.setItem('userSettingsTab', this.id);
                });
            });

            // When the page is loaded, retrieve the selected tab from localStorage
            var selectedTabId = localStorage.getItem('userSettingsTab');
            if (selectedTabId) {
                var selectedTab = document.getElementById(selectedTabId);
                // Trigger a click event on the selected tab
                selectedTab.click();
            }
        });

        $(document).ready(function () {
            $('#branch').select2({
                placeholder: "@lang('translation.users.choose_branch')",
                allowClear: true,
                multiple: true,
                closeOnSelect: false,
            });
        });
    </script>
@endsection