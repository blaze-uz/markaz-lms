@extends('layouts.master')
@section('title')
@lang('translation.staff_settings')
@endsection
@section('content')
{{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}
<livewire:settings.staff-settings />

<div class="mt-4 row justify-content-center">
    <div class="col-md-2"></div>
    <div class="col-md-8">
        <div class="card">
            <div class="card-header align-items-center d-flex">
                <h4 class="mb-0 card-title flex-grow-1">@lang('translation.telegram.staff_twa_settings')</h4>
            </div>
            <div class="card-body">
                <div class="live-preview">
                    <form class="row g-3" action="{{ route('admin.settings.telegram.set-token') }}" method="POST">
                        @csrf
                        <div class="col-12">
                            <div class="form-group">
                                <label for="bot_token"
                                    class="form-label">@lang('translation.telegram.bot_token')</label>
                                <input type="text" class="form-control" id="bot_token" name="bot_token"
                                    value="{{ old('bot_token', $bot_token) }}" required>
                                @error('bot_token')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
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
    <div class="col-md-2"></div>
</div>
@endsection