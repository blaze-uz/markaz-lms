@extends('layouts.master')
@section('title')
    @lang('translation.sms.sms')
@endsection
@section('content')
    {{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}

    <div class="row">
        <div class="col-lg-8">
            <div class="card rounded-4">
                <div class="card-header rounded-top-4">
                    <!-- Desktop Header -->
                    <div class="d-none d-md-flex justify-content-between align-items-center">
                        <div class="card-title mb-0">
                            <h4 class="mb-0">@lang('translation.sms.template')</h4>
                            <small class="text-muted">#{{ $template->id }}</small>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <form action="{{ route('admin.settings.sms.check-status', $template) }}" method="POST"
                                class="d-inline-block">
                                @csrf
                                <button type="submit" class="btn bg-info-subtle text-info rounded-3" data-bs-toggle="tooltip"
                                    title="{{ __('translation.sms.check_status') }}">
                                    <i class="fas fa-rotate me-1"></i>
                                    @lang('translation.sms.check_status')
                                </button>
                            </form>
                            @if ($template->status !== 'moderation')
                                <a href="{{ route('admin.settings.sms.edit', $template) }}"
                                    class="btn bg-success-subtle text-success rounded-3" data-bs-toggle="tooltip"
                                    title="{{ __('translation.sms.edit') }}">
                                    <i class="fa-regular fa-edit me-1"></i>
                                    @lang('translation.sms.edit')
                                </a>
                            @endif
                            <form action="{{ route('admin.settings.sms.destroy', $template) }}" method="POST"
                                class="d-inline-block">
                                @csrf
                                @method('DELETE')
                                <button onclick="return confirm('Are you sure?')"
                                    class="btn bg-danger-subtle text-danger rounded-3" data-bs-toggle="tooltip"
                                    title="{{ __('translation.sms.delete') }}">
                                    <i class="fa-regular fa-trash-alt me-1"></i>
                                    @lang('translation.sms.delete')
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Mobile Header -->
                    <div class="d-md-none">
                        <div class="text-center mb-3">
                            <h4 class="mb-1">@lang('translation.sms.template')</h4>
                            <small class="text-muted">#{{ $template->id }}</small>
                        </div>
                        <div class="d-grid gap-2">
                            <form action="{{ route('admin.settings.sms.check-status', $template) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn bg-info-subtle text-info rounded-3 w-100">
                                    <i class="fas fa-rotate me-2"></i>
                                    @lang('translation.sms.check_status')
                                </button>
                            </form>
                            @if ($template->status !== 'moderation')
                                <a href="{{ route('admin.settings.sms.edit', $template) }}"
                                    class="btn bg-success-subtle text-success rounded-3">
                                    <i class="fa-regular fa-edit me-2"></i>
                                    @lang('translation.sms.edit')
                                </a>
                            @endif
                            <form action="{{ route('admin.settings.sms.destroy', $template) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button onclick="return confirm('Are you sure?')"
                                    class="btn bg-danger-subtle text-danger rounded-3 w-100">
                                    <i class="fa-regular fa-trash-alt me-2"></i>
                                    @lang('translation.sms.delete')
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Desktop Layout -->
                    <div class="d-none d-md-block">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded-3 h-100 d-flex flex-column">
                                    <label class="form-label fw-semibold text-muted mb-2">@lang('translation.sms.template')</label>
                                    <div class="fw-medium flex-grow-1">{{ $template->name }}</div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="p-3 bg-light rounded-3 h-100 d-flex flex-column">
                                    <label class="form-label fw-semibold text-muted mb-2">@lang('translation.sms.content')</label>
                                    <div class="fw-medium flex-grow-1">{{ $template->content }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded-3 h-100 d-flex flex-column">
                                    <label class="form-label fw-semibold text-muted mb-2">@lang('translation.sms.status')</label>
                                    <div class="flex-grow-1 d-flex align-items-center">
                                        <span
                                            class="badge badge-subtle 
                                            @if ($template->status === 'registered') bg-success-subtle text-success
                                            @elseif($template->status === 'moderation') bg-warning-subtle text-warning
                                            @else bg-danger-subtle text-danger @endif">
                                            {{ __('translation.sms.status_' . $template->status) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            @if ($template->eskiz_status)
                                <div class="col-md-6">
                                    <div class="p-3 bg-light rounded-3 h-100 d-flex flex-column">
                                        <label class="form-label fw-semibold text-muted mb-2">@lang('translation.sms.eskiz_status')</label>
                                        <div class="flex-grow-1 d-flex align-items-center">
                                            <span class="badge badge-subtle bg-info-subtle text-info">
                                                {{ __('translation.sms.eskiz_status_' . $template->eskiz_status) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Mobile Layout -->
                    <div class="d-md-none">
                        <div class="d-grid gap-3">
                            <!-- Template Name -->
                            <div class="p-3 bg-light rounded-3">
                                <label class="form-label fw-semibold text-muted mb-2 small">@lang('translation.sms.template')</label>
                                <div class="fw-medium">{{ $template->name }}</div>
                            </div>

                            <!-- Type and Status Row -->
                            <div class="row g-2">
                                <div class="col-12">
                                    <div class="p-3 bg-light rounded-3">
                                        <label
                                            class="form-label fw-semibold text-muted mb-2 small">@lang('translation.sms.status')</label>
                                        <div>
                                            <span
                                                class="badge badge-subtle 
                                                @if ($template->status === 'registered') bg-success-subtle text-success
                                                @elseif($template->status === 'moderation') bg-warning-subtle text-warning
                                                @else bg-danger-subtle text-danger @endif">
                                                {{ __('translation.sms.status_' . $template->status) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Eskiz Status (if exists) -->
                            @if ($template->eskiz_status)
                                <div class="p-3 bg-light rounded-3">
                                    <label class="form-label fw-semibold text-muted mb-2 small">@lang('translation.sms.eskiz_status')</label>
                                    <div>
                                        <span class="badge badge-subtle bg-info-subtle text-info">
                                            {{ __('translation.sms.eskiz_status_' . $template->eskiz_status) }}
                                        </span>
                                    </div>
                                </div>
                            @endif

                            <!-- Content -->
                            <div class="p-3 bg-light rounded-3">
                                <label class="form-label fw-semibold text-muted mb-2 small">@lang('translation.sms.content')</label>
                                <div class="fw-medium" style="word-wrap: break-word; white-space: pre-wrap;">
                                    {{ $template->content }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card rounded-4">
                <div class="card-header rounded-top-4">
                    <h5 class="card-title mb-0">@lang('translation.sms.moderation_response')</h5>
                </div>
                <div class="card-body">
                    <!-- Desktop Layout -->
                    <div class="d-none d-md-block">
                        @if ($template->sent_to_moderation_at)
                            <div class="mb-3">
                                <label class="form-label fw-semibold text-muted mb-2">@lang('translation.sms.sent_to_moderation_at')</label>
                                <div class="p-3 bg-light rounded-3 h-100 d-flex align-items-center">
                                    <i class="fas fa-clock me-2 text-primary"></i>
                                    {{ dateFormat($template->sent_to_moderation_at, 'd.m.Y H:i') }}
                                </div>
                            </div>
                        @endif

                        @if ($template->moderation_response_at)
                            <div class="mb-3">
                                <label class="form-label fw-semibold text-muted mb-2">@lang('translation.sms.moderation_response_at')</label>
                                <div class="p-3 bg-light rounded-3 h-100 d-flex align-items-center">
                                    <i class="fas fa-check-circle me-2 text-success"></i>
                                    {{ dateFormat($template->moderation_response_at, 'd.m.Y H:i') }}
                                </div>
                            </div>
                        @endif

                        @if ($template->moderation_response)
                            <div class="mb-3">
                                <label class="form-label fw-semibold text-muted mb-2">@lang('translation.sms.moderation_response')</label>
                                <div class="p-3 bg-light rounded-3 h-100" style="max-height: 300px; overflow-y: auto;">
                                    <pre class="mb-0 small">{{ json_encode(json_decode($template->moderation_response), JSON_PRETTY_PRINT) }}</pre>
                                </div>
                            </div>
                        @endif

                        @if (!$template->sent_to_moderation_at && !$template->moderation_response_at && !$template->moderation_response)
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-info-circle fa-2x mb-3"></i>
                                <p class="mb-0">@lang('translation.sms.no_moderation_data')</p>
                            </div>
                        @endif
                    </div>

                    <!-- Mobile Layout -->
                    <div class="d-md-none">
                        <div class="d-grid gap-3">
                            @if ($template->sent_to_moderation_at)
                                <div class="p-3 bg-light rounded-3">
                                    <label class="form-label fw-semibold text-muted mb-2 small">@lang('translation.sms.sent_to_moderation_at')</label>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-clock me-2 text-primary"></i>
                                        <span
                                            class="small">{{ dateFormat($template->sent_to_moderation_at, 'd.m.Y H:i') }}</span>
                                    </div>
                                </div>
                            @endif

                            @if ($template->moderation_response_at)
                                <div class="p-3 bg-light rounded-3">
                                    <label class="form-label fw-semibold text-muted mb-2 small">@lang('translation.sms.moderation_response_at')</label>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-check-circle me-2 text-success"></i>
                                        <span
                                            class="small">{{ dateFormat($template->moderation_response_at, 'd.m.Y H:i') }}</span>
                                    </div>
                                </div>
                            @endif

                            @if ($template->moderation_response)
                                <div class="p-3 bg-light rounded-3">
                                    <label class="form-label fw-semibold text-muted mb-2 small">@lang('translation.sms.moderation_response')</label>
                                    <div style="max-height: 200px; overflow-y: auto;">
                                        <pre class="mb-0 small" style="white-space: pre-wrap; word-wrap: break-word;">{{ json_encode(json_decode($template->moderation_response), JSON_PRETTY_PRINT) }}</pre>
                                    </div>
                                </div>
                            @endif

                            @if (!$template->sent_to_moderation_at && !$template->moderation_response_at && !$template->moderation_response)
                                <div class="text-center text-muted py-3">
                                    <i class="fas fa-info-circle fa-lg mb-2"></i>
                                    <p class="mb-0 small">@lang('translation.sms.no_moderation_data')</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
