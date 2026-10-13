@extends('layouts.master')
@section('title')
    @lang('translation.telegram.student_twa_settings')
@endsection
@section('content')
    {{ \Diglactic\Breadcrumbs\Breadcrumbs::render('admin.settings.students-telegram-webapp') }}

    <div class="row justify-content-center">
        <div class="col-xxl-6">
            <div class="card">
                <div class="card-header align-items-center d-flex">
                    <h4 class="mb-0 card-title flex-grow-1">@lang('translation.telegram.student_twa_settings')</h4>
                </div>
                <div class="card-body">
                    <div class="live-preview">
                        <form class="row g-3" action="{{ route('admin.settings.students-telegram-webapp.update') }}"
                            method="POST">
                            @csrf
                            @method('PUT')
                            <div class="col-12">
                                <label for="bot_token" class="form-label">@lang('translation.telegram.bot_token')</label>
                                <input type="text" class="form-control" id="bot_token" name="bot_token"
                                    value="{{ old('bot_token', $settings->value ?? '') }}"
                                    placeholder="BOT_TOKEN">
                                @error('bot_token')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <div class="border card">
                                    <div class="card-header">
                                        <h5 class="mb-0 card-title">
                                            <i class="align-middle ri-information-line me-2"></i>
                                            @lang('translation.instruction')
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-4">
                                            <h6>@lang('translation.how_to_get_bot_token'):</h6>
                                            <ol class="mb-0">
                                                <li>@lang('translation.telegram.open_botfather')
                                                    <a href="https://t.me/BotFather" target="_blank"
                                                        class="fw-medium">@BotFather <i
                                                            class="align-middle ri-external-link-line"></i></a>
                                                </li>
                                                <li>@lang('translation.telegram.send_newbot')</li>
                                                <li>@lang('translation.telegram.choose_name')</li>
                                                <li>@lang('translation.telegram.choose_username')</li>
                                                <li>@lang('translation.telegram.copy_token')</li>
                                            </ol>
                                        </div>
                                        <div class="alert alert-info" role="alert">
                                            <i class="align-middle ri-information-line me-2"></i>
                                            @lang('translation.telegram.token_security_note')
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if (!str_starts_with(config('app.url'), 'https://'))
                                <div class="col-12">
                                    <div class="alert alert-danger" role="alert">
                                        <i class="align-middle ri-error-warning-line me-2"></i>
                                        <strong>@lang('translation.warning'):</strong>
                                        @lang('translation.telegram.https_required_warning')
                                        <div class="mt-2">
                                            <small class="text-danger">
                                                @lang('translation.telegram.contact_technical_department')
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="col-12 d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="align-bottom ri-save-line me-1"></i>
                                    @lang('translation.save')
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
