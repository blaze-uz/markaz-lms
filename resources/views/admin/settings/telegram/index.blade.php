@extends('layouts.master')
@section('title')
    @lang('translation.telegram_integration')
@endsection
@push('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.css"
        integrity="sha512-H9jrZiiopUdsLpg94A333EfumgUBpO9MdbxStdeITo+KEIMaNfHNvwyjjDJb+ERPaRS6DpyRlKbvPUasNItRyw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
@endpush
@section('content')
    {{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}

    @if ($telegram_bot_token)
        <div class="row">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="text-center nav flex-column nav-pills" id="v-pills-tab" role="tablist"
                            aria-orientation="vertical">
                            <a class="gap-1 mb-2 nav-link active align-items-baseline d-flex align-items-center justify-content-center"
                                id="v-telegram-ids" data-bs-toggle="pill" href="#telegram-ids" role="tab"
                                aria-controls="v-pills-home" aria-selected="false" tabindex="-1">
                                <i class="ri-settings-line"></i> @lang("translation.telegram.ids")
                            </a>
                            <a class="gap-1 mb-2 nav-link d-flex align-items-center justify-content-center"
                                id="v-telegram-daily-report" data-bs-toggle="pill" href="#telegram-daily-report" role="tab"
                                aria-controls="v-pills-profile" aria-selected="false" tabindex="-1">
                                <i class="ri-timer-line"></i>
                                @lang("translation.telegram.daily_report_settings")
                            </a>
                            <a class="gap-1 mb-2 nav-link d-flex align-items-center justify-content-center"
                                id="v-telegram-bot-token" data-bs-toggle="pill" href="#telegram-bot-token" role="tab"
                                aria-controls="v-pills-profile" aria-selected="false" tabindex="-1">
                                <i class="ri-telegram-line"></i>
                                @lang("translation.telegram.bot_token")
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-9">
                <div class="mt-4 tab-content text-muted mt-md-0" id="v-pills-tabContent">
                    <div class="tab-pane fade active show" id="telegram-ids" role="tabpanel" aria-labelledby="v-telegram-ids">
                        <livewire:telegram-table lazy />
                    </div>
                    <div class="tab-pane fade" id="telegram-daily-report" role="tabpanel"
                        aria-labelledby="v-telegram-daily-report">
                        <div class="alert bg-info-subtle text-info d-flex justify-content-between align-items-center">
                            @lang("translation.telegram.send_daily_report")
                            <button class="gap-2 btn btn-info btn-sm d-flex align-items-center" data-bs-toggle="modal"
                                data-bs-target="#sendMessageModal">
                                <i class="ri-send-plane-2-line"></i>
                                @lang("translation.telegram.send")
                            </button>
                        </div>
                        <form action="{{ route('admin.telegrams.set-daily-report') }}" method="POST">
                            @csrf
                            <livewire:settings.telegram-daily-report-settings />
                            <div class="d-flex justify-content-end">
                                <button class="btn btn-primary">
                                    @lang("translation.btn.save")
                                </button>
                            </div>
                        </form>
                        <livewire:settings.telegram-daily-report-items />
                    </div>
                    <div class="tab-pane fade" id="telegram-bot-token" role="tabpanel"
                        aria-labelledby="v-telegram-bot-token">
                        <div class="card">
                            <div class="card-body">
                                <form action="{{ route('admin.settings.telegram.set-token') }}" method="POST">
                                    @csrf
                                    <div class="mb-3 form-group">
                                        <label for="telegram-bot-token">
                                            @lang("translation.telegram.bot_token")
                                        </label>
                                        <input type="text" class="form-control" id="telegram-bot-token"
                                            name="telegram_bot_token"
                                            placeholder="@lang('translation.telegram.bot_token')" value="{{ old('telegram_bot_token') ?? $telegram_bot_token ?? '' }}">
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
            </div>
        </div>
    @else
        <div class="row">
            <div class="col-lg-3"></div>
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">@lang('translation.telegram.bot_settings')</h4>
                        <form action="{{ route('admin.settings.telegram.set-token') }}" method="POST" class="needs-validation" novalidate>
                            @csrf
                            <div class="mb-3">
                                <label for="telegram_bot_token" class="form-label">@lang('translation.telegram.bot_token')</label>
                                <input type="text" class="form-control" id="telegram_bot_token" name="telegram_bot_token"
                                        value="{{ $telegram_bot_token }}" required>
                                <div class="invalid-feedback">
                                    @lang('translation.telegram.please_enter_token')
                                </div>
                                <div class="form-text">
                                    @lang('translation.telegram.token_instructions')
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-lg-8">
                                    <div class="alert alert-info">
                                        <h5 class="alert-heading"><i class="ri-information-line me-2"></i>@lang('translation.telegram.how_to_get_token')</h5>
                                        <ol class="mb-0">
                                            <li>@lang('translation.telegram.step1_botfather')</li>
                                            <li>@lang('translation.telegram.step2_newbot')</li>
                                            <li>@lang('translation.telegram.step3_name')</li>
                                            <li>@lang('translation.telegram.step4_username')</li>
                                            <li>@lang('translation.telegram.step5_copy_token')</li>
                                        </ol>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="text-center">
                                        <a href="{{ asset('images/telegram-bot-instruction.svg') }}" class="instruction-image position-relative d-inline-block"
                                            data-fancybox="images">
                                            <img src="{{ asset('images/telegram-bot-instruction.svg') }}" alt="Telegram bot instruction"
                                                class="img-thumbnail object-fit-cover" style="width: 200px; height: 160px"
                                                onmouseover="this.style.cursor='pointer'; this.style.opacity='0.7'"
                                                onmouseout="this.style.cursor='default'; this.style.opacity='1'">
                                            <div class="position-absolute top-50 start-50 translate-middle bg-white rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                <i class="ri-zoom-in-line text-primary"></i>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-3 d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="ri-save-line align-middle me-1"></i>
                                    @lang('attr.save')
                                </button>
                            </div>
                        </form>

                        @if($telegram_bot_token)
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="alert alert-success">
                                        <h5 class="alert-heading"><i class="ri-check-line me-2"></i>@lang('translation.telegram.bot_configured')</h5>
                                        <p class="mb-0">@lang('translation.telegram.bot_ready')</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-lg-3"></div>
        </div>
    @endif
    @include('admin.settings.telegram.partials.send-message-modal')
@endsection
@section('script-bottom')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.js"></script>
    <script>
        $(".instruction-image").fancybox();
    </script>
    <script>
        // When a tab is clicked, store its id in localStorage
        document.querySelectorAll('.nav-link').forEach(function (tab) {
            tab.addEventListener('click', function () {
                localStorage.setItem('selectedTelegramSettingsTab', this.id);
            });
        });

        // When the page is loaded, retrieve the selected tab from localStorage
        var selectedTabId = localStorage.getItem('selectedTelegramSettingsTab');
        if (selectedTabId) {
            var selectedTab = document.getElementById(selectedTabId);
            // Trigger a click event on the selected tab
            selectedTab.click();
        }

        //set send_date value to today
        document.getElementById('send_date').valueAsDate = new Date();
    </script>
@endsection
