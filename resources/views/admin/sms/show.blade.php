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
                            <h4 class="mb-0">@lang('translation.sms.sms')</h4>
                            <small class="text-muted">#{{ $sms->id }}</small>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            @if ($sms->status === 'pending' || $sms->status === 'scheduled')
                                <form action="{{ route('admin.sms.send-single', $sms->id) }}" method="POST"
                                    class="d-inline-block">
                                    @csrf
                                    <button type="submit" class="btn bg-success-subtle text-success rounded-3"
                                        data-bs-toggle="tooltip" title="{{ __('translation.sms.send_now') }}"
                                        onclick="return confirm('Are you sure?')">
                                        <i class="ri-send-plane-2-line me-1"></i>
                                        @lang('translation.sms.send_now')
                                    </button>
                                </form>
                            @endif
                            @if ($sms->status != 'pending')
                                <form action="{{ route('admin.sms.return-to-pending') }}" method="POST"
                                    class="d-inline-block">
                                    @csrf
                                    <input type="hidden" name="ids" value="{{ $sms->id }}">
                                    <button type="submit" class="btn bg-warning-subtle text-warning rounded-3"
                                        data-bs-toggle="tooltip" title="{{ __('translation.return_to_pending') }}">
                                        <i class="ri-arrow-go-back-line me-1"></i>
                                        @lang('translation.return_to_pending')
                                    </button>
                                </form>
                            @endif
                            <form action="{{ route('admin.sms.delete') }}" method="POST" class="d-inline-block">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="ids" value="{{ $sms->id }}">
                                <button type="submit" class="btn bg-danger-subtle text-danger rounded-3"
                                    data-bs-toggle="tooltip" title="{{ __('translation.btn.delete') }}">
                                    <i class="ri-delete-bin-2-line me-1"></i>
                                    @lang('translation.btn.delete')
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Mobile Header -->
                    <div class="d-md-none">
                        <div class="text-center mb-3">
                            <h4 class="mb-1">@lang('translation.sms.sms')</h4>
                            <small class="text-muted">#{{ $sms->id }}</small>
                        </div>
                        <div class="d-grid gap-2">
                            @if ($sms->status === 'pending' || $sms->status === 'scheduled')
                                <form action="{{ route('admin.sms.send-single', $sms->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn bg-success-subtle text-success rounded-3 w-100"
                                        onclick="return confirm('Are you sure?')">
                                        <i class="ri-send-plane-2-line me-2"></i>
                                        @lang('translation.sms.send_now')
                                    </button>
                                </form>
                            @endif
                            @if ($sms->status != 'pending')
                                <form action="{{ route('admin.sms.return-to-pending') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="ids" value="{{ $sms->id }}">
                                    <button type="submit" class="btn bg-warning-subtle text-warning rounded-3 w-100">
                                        <i class="ri-arrow-go-back-line me-2"></i>
                                        @lang('translation.return_to_pending')
                                    </button>
                                </form>
                            @endif
                            <form action="{{ route('admin.sms.delete') }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="ids" value="{{ $sms->id }}">
                                <button type="submit" class="btn bg-danger-subtle text-danger rounded-3 w-100">
                                    <i class="ri-delete-bin-2-line me-2"></i>
                                    @lang('translation.btn.delete')
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
                                    <label class="form-label fw-semibold text-muted mb-2">@lang('translation.students.name')</label>
                                    <div class="fw-medium flex-grow-1">
                                        {{ $sms->smsable?->name . ' ' . $sms->smsable?->surname }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded-3 h-100 d-flex flex-column">
                                    <label class="form-label fw-semibold text-muted mb-2">@lang('translation.students.phone')</label>
                                    <div class="fw-medium flex-grow-1">{{ $sms->phone }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded-3 h-100 d-flex flex-column">
                                    <label class="form-label fw-semibold text-muted mb-2">@lang('attr.status')</label>
                                    <div class="flex-grow-1 d-flex align-items-center">
                                        <span @class([
                                            'badge badge-subtle',
                                            'bg-success-subtle text-success' => $sms->status === 'sent',
                                            'bg-warning-subtle text-warning' => $sms->status === 'pending',
                                            'bg-danger-subtle text-danger' => $sms->status === 'failed',
                                            'bg-primary-subtle text-primary' => $sms->status === 'scheduled',
                                        ])>
                                            @if ($sms->status === 'sent')
                                                <i class="mdi mdi-check me-1"></i>
                                            @elseif($sms->status === 'pending')
                                                <i class="mdi mdi-clock me-1"></i>
                                            @elseif($sms->status === 'failed')
                                                <i class="mdi mdi-close me-1"></i>
                                            @elseif($sms->status === 'scheduled')
                                                <i class="mdi mdi-calendar me-1"></i>
                                            @endif
                                            {{ __('translation.sms.' . $sms->status) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded-3 h-100 d-flex flex-column">
                                    <label class="form-label fw-semibold text-muted mb-2">External ID</label>
                                    <div class="fw-medium flex-grow-1">{{ $sms->external_id ?: 'N/A' }}</div>
                                </div>
                            </div>
                            @if ($sms->status === 'sent')
                                <div class="col-md-6">
                                    <div class="p-3 bg-light rounded-3 h-100 d-flex flex-column">
                                        <label class="form-label fw-semibold text-muted mb-2">@lang('translation.sms.send_at')</label>
                                        <div class="fw-medium flex-grow-1 d-flex align-items-center">
                                            <i class="fas fa-check-circle me-2 text-success"></i>
                                            {{ dateFormat($sms->sent_at, 'd.m.Y H:i') }}
                                        </div>
                                    </div>
                                </div>
                            @endif
                            @if ($sms->status === 'scheduled')
                                <div class="col-md-6">
                                    <div class="p-3 bg-light rounded-3 h-100 d-flex flex-column">
                                        <label class="form-label fw-semibold text-muted mb-2">@lang('translation.sms.left_time_to_send')</label>
                                        <div class="fw-medium flex-grow-1 d-flex align-items-center">
                                            <i class="fas fa-clock me-2 text-primary"></i>
                                            {{ Carbon\Carbon::parse($sms->scheduled_at)->diffForHumans() }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 bg-light rounded-3 h-100 d-flex flex-column">
                                        <label class="form-label fw-semibold text-muted mb-2">@lang('translation.sms.scheduled_at')</label>
                                        <div class="fw-medium flex-grow-1 d-flex align-items-center">
                                            <i class="fas fa-calendar me-2 text-primary"></i>
                                            {{ dateFormat($sms->scheduled_at, 'd.m.Y H:i') }}
                                        </div>
                                    </div>
                                </div>
                            @endif
                            <div class="col-12">
                                <div class="p-3 bg-light rounded-3 h-100 d-flex flex-column">
                                    <label class="form-label fw-semibold text-muted mb-2">@lang('translation.sms.content')</label>
                                    <div class="fw-medium flex-grow-1"
                                        style="word-wrap: break-word; white-space: pre-wrap;">{{ $sms->content }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Mobile Layout -->
                    <div class="d-md-none">
                        <div class="d-grid gap-3">
                            <!-- Name -->
                            <div class="p-3 bg-light rounded-3">
                                <label class="form-label fw-semibold text-muted mb-2 small">@lang('translation.users.name')</label>
                                <div class="fw-medium">{{ $sms->smsable?->name . ' ' . $sms->smsable?->surname }}</div>
                            </div>

                            <!-- Phone and Status Row -->
                            <div class="row g-2">
                                <div class="col-6">
                                    <div class="p-3 bg-light rounded-3">
                                        <label
                                            class="form-label fw-semibold text-muted mb-2 small">@lang('translation.students.phone')</label>
                                        <div class="fw-medium">{{ $sms->phone }}</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-3 bg-light rounded-3">
                                        <label
                                            class="form-label fw-semibold text-muted mb-2 small">@lang('attr.status')</label>
                                        <div>
                                            <span @class([
                                                'badge badge-subtle',
                                                'bg-success-subtle text-success' => $sms->status === 'sent',
                                                'bg-warning-subtle text-warning' => $sms->status === 'pending',
                                                'bg-danger-subtle text-danger' => $sms->status === 'failed',
                                                'bg-primary-subtle text-primary' => $sms->status === 'scheduled',
                                            ])>
                                                @if ($sms->status === 'sent')
                                                    <i class="mdi mdi-check me-1"></i>
                                                @elseif($sms->status === 'pending')
                                                    <i class="mdi mdi-clock me-1"></i>
                                                @elseif($sms->status === 'failed')
                                                    <i class="mdi mdi-close me-1"></i>
                                                @elseif($sms->status === 'scheduled')
                                                    <i class="mdi mdi-calendar me-1"></i>
                                                @endif
                                                {{ __('translation.sms.' . $sms->status) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- External ID -->
                            <div class="p-3 bg-light rounded-3">
                                <label class="form-label fw-semibold text-muted mb-2 small">External ID</label>
                                <div class="fw-medium">{{ $sms->external_id ?: 'N/A' }}</div>
                            </div>

                            <!-- Status-specific information -->
                            @if ($sms->status === 'sent')
                                <div class="p-3 bg-light rounded-3">
                                    <label class="form-label fw-semibold text-muted mb-2 small">@lang('translation.sms.send_at')</label>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-check-circle me-2 text-success"></i>
                                        <span class="small">{{ dateFormat($sms->sent_at, 'd.m.Y H:i') }}</span>
                                    </div>
                                </div>
                            @endif

                            @if ($sms->status === 'scheduled')
                                <div class="row g-2">
                                    <div class="col-6">
                                        <div class="p-3 bg-light rounded-3">
                                            <label
                                                class="form-label fw-semibold text-muted mb-2 small">@lang('translation.sms.left_time_to_send')</label>
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-clock me-2 text-primary"></i>
                                                <span
                                                    class="small">{{ Carbon\Carbon::parse($sms->scheduled_at)->diffForHumans() }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="p-3 bg-light rounded-3">
                                            <label
                                                class="form-label fw-semibold text-muted mb-2 small">@lang('translation.sms.scheduled_at')</label>
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-calendar me-2 text-primary"></i>
                                                <span
                                                    class="small">{{ dateFormat($sms->scheduled_at, 'd.m.Y H:i') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- Content -->
                            <div class="p-3 bg-light rounded-3">
                                <label class="form-label fw-semibold text-muted mb-2 small">@lang('translation.sms.content')</label>
                                <div class="fw-medium" style="word-wrap: break-word; white-space: pre-wrap;">
                                    {{ $sms->content }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Eskiz Data Card -->
            <div class="card rounded-4 mb-3">
                <div class="card-header rounded-top-4">
                    <h5 class="card-title mb-0">@lang('translation.sms.eskiz_data')</h5>
                </div>
                <div class="card-body">
                    <!-- Desktop Layout -->
                    <div class="d-none d-md-block">
                        <div class="d-grid gap-3">
                            @if ($sms->eskiz_status)
                                <div class="p-3 bg-light rounded-3">
                                    <label class="form-label fw-semibold text-muted mb-2">@lang('translation.sms.eskiz_status')</label>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-info-circle me-2 text-info"></i>
                                        <span class="badge badge-subtle bg-info-subtle text-info">
                                            {{ $sms->eskiz_status }}
                                        </span>
                                    </div>
                                </div>
                            @endif

                            @if ($sms->parts_count)
                                <div class="p-3 bg-light rounded-3">
                                    <label class="form-label fw-semibold text-muted mb-2">@lang('translation.sms.parts_count')</label>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-puzzle-piece me-2 text-primary"></i>
                                        <span class="fw-medium">{{ $sms->parts_count }}</span>
                                    </div>
                                </div>
                            @endif

                            @if ($sms->price)
                                <div class="p-3 bg-light rounded-3">
                                    <label class="form-label fw-semibold text-muted mb-2">@lang('translation.sms.price')</label>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-dollar-sign me-2 text-success"></i>
                                        <span class="fw-medium">{{ money($sms->price) }}</span>
                                    </div>
                                </div>
                            @endif

                            @if ($sms->total_price)
                                <div class="p-3 bg-light rounded-3">
                                    <label class="form-label fw-semibold text-muted mb-2">@lang('translation.sms.total_price')</label>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-calculator me-2 text-success"></i>
                                        <span class="fw-medium">{{ money($sms->total_price) }}</span>
                                    </div>
                                </div>
                            @endif

                            @if ($sms->character_count)
                                <div class="p-3 bg-light rounded-3">
                                    <label class="form-label fw-semibold text-muted mb-2">@lang('translation.sms.character_count')</label>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-font me-2 text-warning"></i>
                                        <span class="fw-medium">{{ $sms->character_count }}</span>
                                    </div>
                                </div>
                            @endif

                            @if ($sms->is_ad !== null)
                                <div class="p-3 bg-light rounded-3">
                                    <label class="form-label fw-semibold text-muted mb-2">@lang('translation.sms.is_ad')</label>
                                    <div class="d-flex align-items-center">
                                        <i
                                            class="fas fa-bullhorn me-2 {{ $sms->is_ad ? 'text-warning' : 'text-muted' }}"></i>
                                        <span
                                            class="badge badge-subtle {{ $sms->is_ad ? 'bg-warning-subtle text-warning' : 'bg-secondary-subtle text-secondary' }}">
                                            {{ $sms->is_ad ? __('translation.sms.yes') : __('translation.sms.no') }}
                                        </span>
                                    </div>
                                </div>
                            @endif

                            @if ($sms->channel)
                                <div class="p-3 bg-light rounded-3">
                                    <label class="form-label fw-semibold text-muted mb-2">@lang('translation.sms.channel')</label>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-broadcast-tower me-2 text-info"></i>
                                        <span class="fw-medium">{{ $sms->channel }}</span>
                                    </div>
                                </div>
                            @endif

                            @if ($sms->result_date)
                                <div class="p-3 bg-light rounded-3">
                                    <label class="form-label fw-semibold text-muted mb-2">@lang('translation.sms.result_date')</label>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-calendar-check me-2 text-success"></i>
                                        <span class="fw-medium">{{ dateFormat($sms->result_date, 'd.m.Y H:i') }}</span>
                                    </div>
                                </div>
                            @endif

                            @if (
                                !$sms->eskiz_status &&
                                    !$sms->parts_count &&
                                    !$sms->price &&
                                    !$sms->total_price &&
                                    !$sms->character_count &&
                                    $sms->is_ad === null &&
                                    !$sms->channel &&
                                    !$sms->result_date)
                                <div class="text-center text-muted py-4">
                                    <i class="fas fa-info-circle fa-2x mb-3"></i>
                                    <p class="mb-0">@lang('translation.sms.no_eskiz_data')</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Mobile Layout -->
                    <div class="d-md-none">
                        <div class="d-grid gap-3">
                            @if ($sms->eskiz_status)
                                <div class="p-3 bg-light rounded-3">
                                    <label class="form-label fw-semibold text-muted mb-2 small">@lang('translation.sms.eskiz_status')</label>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-info-circle me-2 text-info"></i>
                                        <span class="badge badge-subtle bg-info-subtle text-info small">
                                            {{ $sms->eskiz_status }}
                                        </span>
                                    </div>
                                </div>
                            @endif

                            @if ($sms->parts_count)
                                <div class="p-3 bg-light rounded-3">
                                    <label class="form-label fw-semibold text-muted mb-2 small">@lang('translation.sms.parts_count')</label>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-puzzle-piece me-2 text-primary"></i>
                                        <span class="fw-medium small">{{ $sms->parts_count }}</span>
                                    </div>
                                </div>
                            @endif

                            @if ($sms->price)
                                <div class="p-3 bg-light rounded-3">
                                    <label class="form-label fw-semibold text-muted mb-2 small">@lang('translation.sms.price')</label>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-dollar-sign me-2 text-success"></i>
                                        <span class="fw-medium small">{{ money($sms->price) }}</span>
                                    </div>
                                </div>
                            @endif

                            @if ($sms->total_price)
                                <div class="p-3 bg-light rounded-3">
                                    <label class="form-label fw-semibold text-muted mb-2 small">@lang('translation.sms.total_price')</label>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-calculator me-2 text-success"></i>
                                        <span class="fw-medium small">{{ money($sms->total_price) }}</span>
                                    </div>
                                </div>
                            @endif

                            @if ($sms->character_count)
                                <div class="p-3 bg-light rounded-3">
                                    <label class="form-label fw-semibold text-muted mb-2 small">@lang('translation.sms.character_count')</label>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-font me-2 text-warning"></i>
                                        <span class="fw-medium small">{{ $sms->character_count }}</span>
                                    </div>
                                </div>
                            @endif

                            @if ($sms->is_ad !== null)
                                <div class="p-3 bg-light rounded-3">
                                    <label class="form-label fw-semibold text-muted mb-2 small">@lang('translation.sms.is_ad')</label>
                                    <div class="d-flex align-items-center">
                                        <i
                                            class="fas fa-bullhorn me-2 {{ $sms->is_ad ? 'text-warning' : 'text-muted' }}"></i>
                                        <span
                                            class="badge badge-subtle {{ $sms->is_ad ? 'bg-warning-subtle text-warning' : 'bg-secondary-subtle text-secondary' }} small">
                                            {{ $sms->is_ad ? __('translation.sms.yes') : __('translation.sms.no') }}
                                        </span>
                                    </div>
                                </div>
                            @endif

                            @if ($sms->channel)
                                <div class="p-3 bg-light rounded-3">
                                    <label class="form-label fw-semibold text-muted mb-2 small">@lang('translation.sms.channel')</label>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-broadcast-tower me-2 text-info"></i>
                                        <span class="fw-medium small">{{ $sms->channel }}</span>
                                    </div>
                                </div>
                            @endif

                            @if ($sms->result_date)
                                <div class="p-3 bg-light rounded-3">
                                    <label class="form-label fw-semibold text-muted mb-2 small">@lang('translation.sms.result_date')</label>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-calendar-check me-2 text-success"></i>
                                        <span
                                            class="fw-medium small">{{ dateFormat($sms->result_date, 'd.m.Y H:i') }}</span>
                                    </div>
                                </div>
                            @endif

                            @if (
                                !$sms->eskiz_status &&
                                    !$sms->parts_count &&
                                    !$sms->price &&
                                    !$sms->total_price &&
                                    !$sms->character_count &&
                                    $sms->is_ad === null &&
                                    !$sms->channel &&
                                    !$sms->result_date)
                                <div class="text-center text-muted py-3">
                                    <i class="fas fa-info-circle fa-lg mb-2"></i>
                                    <p class="mb-0 small">@lang('translation.sms.no_eskiz_data')</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Error Log Card (if exists) -->
            @if ($sms->error_log)
                <div class="card rounded-4">
                    <div class="card-header rounded-top-4">
                        <h5 class="card-title mb-0">@lang('translation.sms.error_details')</h5>
                    </div>
                    <div class="card-body">
                        <!-- Desktop Layout -->
                        <div class="d-none d-md-block">
                            <div class="p-3 bg-light rounded-3" style="max-height: 300px; overflow-y: auto;">
                                <pre class="mb-0 small text-danger">{{ $sms->error_log }}</pre>
                            </div>
                        </div>

                        <!-- Mobile Layout -->
                        <div class="d-md-none">
                            <div class="p-3 bg-light rounded-3" style="max-height: 200px; overflow-y: auto;">
                                <pre class="mb-0 small text-danger" style="white-space: pre-wrap; word-wrap: break-word;">{{ $sms->error_log }}</pre>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
