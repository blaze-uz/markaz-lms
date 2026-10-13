<div>
    @if (session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            {{ session('error') }}
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h6 class="mb-1">{{ __('translation.calls.student_calls') }}</h6>
            <small class="text-muted">{{ __('translation.calls.track_communications') }}</small>
        </div>
        @can('student-call-add')
            <button wire:click="toggleAddForm" class="btn btn-primary btn-sm">
                <i class="mdi mdi-plus me-1"></i>
                {{ __('translation.calls.add_call') }}
            </button>
        @endcan
    </div>

    @if ($showAddForm)
        <div class="card mb-4 border-primary">
            <div class="card-header bg-primary-subtle">
                <h6 class="card-title mb-0 text-primary">
                    <i class="mdi mdi-phone-plus me-1"></i>
                    {{ __('translation.calls.add_new_call') }}
                </h6>
            </div>
            <div class="card-body">
                <form wire:submit.prevent="addCall">
                    <!-- Status Selection with Buttons -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            {{ __('translation.calls.call_status') }} <span class="text-danger">*</span>
                        </label>
                        <div class="row g-2">
                            <div class="col-6 col-md-3">
                                <input type="radio" class="btn-check" wire:model="status" value="answered"
                                    id="status-answered">
                                <label class="btn btn-outline-success w-100" for="status-answered">
                                    <i class="mdi mdi-phone-check me-1"></i>
                                    <small>{{ __('translation.calls.answered') }}</small>
                                </label>
                            </div>
                            <div class="col-6 col-md-3">
                                <input type="radio" class="btn-check" wire:model="status" value="not_answered"
                                    id="status-not-answered">
                                <label class="btn btn-outline-danger w-100" for="status-not-answered">
                                    <i class="mdi mdi-phone-missed me-1"></i>
                                    <small>{{ __('translation.calls.not_answered') }}</small>
                                </label>
                            </div>
                            <div class="col-6 col-md-3">
                                <input type="radio" class="btn-check" wire:model="status" value="busy"
                                    id="status-busy">
                                <label class="btn btn-outline-warning w-100" for="status-busy">
                                    <i class="mdi mdi-phone-alert me-1"></i>
                                    <small>{{ __('translation.calls.busy') }}</small>
                                </label>
                            </div>
                            <div class="col-6 col-md-3">
                                <input type="radio" class="btn-check" wire:model="status" value="no_answer"
                                    id="status-no-answer">
                                <label class="btn btn-outline-secondary w-100" for="status-no-answer">
                                    <i class="mdi mdi-phone-off me-1"></i>
                                    <small>{{ __('translation.calls.no_answer') }}</small>
                                </label>
                            </div>
                        </div>
                        @error('status')
                            <div class="text-danger mt-1"><small>{{ __($message) }}</small></div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label
                                    class="form-label fw-semibold">{{ __('translation.calls.phone_number') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="mdi mdi-phone"></i></span>
                                    <input type="text" wire:model="phone"
                                        class="form-control @error('phone') is-invalid @enderror"
                                        placeholder="{{ __('translation.calls.phone_placeholder') }}">
                                </div>
                                @error('phone')
                                    <div class="invalid-feedback">{{ __($message) }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label
                                    class="form-label fw-semibold">{{ __('translation.calls.call_duration') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="mdi mdi-clock-outline"></i></span>
                                    <input type="number" wire:model="duration"
                                        class="form-control @error('duration') is-invalid @enderror"
                                        placeholder="{{ __('translation.calls.duration_placeholder') }}"
                                        min="0">
                                    <span class="input-group-text">{{ __('translation.calls.seconds') }}</span>
                                </div>
                                @error('duration')
                                    <div class="invalid-feedback">{{ __($message) }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            {{ __('translation.calls.call_notes') }} <span class="text-danger">*</span>
                        </label>
                        <textarea wire:model="description" class="form-control @error('description') is-invalid @enderror" rows="3"
                            placeholder="{{ __('translation.calls.description_placeholder') }}"></textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ __($message) }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success">
                            <i class="mdi mdi-content-save me-1"></i>
                            {{ __('translation.btn.save') }}
                        </button>
                        <button type="button" wire:click="toggleAddForm" class="btn btn-light">
                            <i class="mdi mdi-close me-1"></i>
                            {{ __('translation.btn.cancel') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Calls Table -->
    @if ($calls->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th width="80">#</th>
                        <th>{{ __('translation.calls.caller') }}</th>
                        <th>{{ __('translation.calls.contact') }}</th>
                        <th>{{ __('translation.calls.status') }}</th>
                        <th>{{ __('translation.calls.duration') }}</th>
                        <th>{{ __('translation.calls.notes') }}</th>
                        <th>{{ __('translation.calls.date_time') }}</th>
                        <th width="100">{{ __('translation.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($calls as $call)
                        <tr>
                            <td>
                                <span class="badge bg-light text-dark">{{ $call->id }}</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div
                                        class="avatar-xs bg-primary-subtle rounded-circle d-flex align-items-center justify-content-center me-2">
                                        <i class="mdi mdi-account text-primary"></i>
                                    </div>
                                    <div>
                                        <div class="fw-medium">{{ $call->user->name ?? __('translation.unknown') }}
                                        </div>
                                        <small class="text-muted">{{ $call->user->email ?? '' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if ($call->phone)
                                    <a href="tel:{{ $call->phone }}" class="text-decoration-none">
                                        <i class="mdi mdi-phone me-1 text-success"></i>
                                        <span class="fw-medium">{{ $call->phone }}</span>
                                    </a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <span
                                    class="badge bg-{{ $call->status_color }}-subtle text-{{ $call->status_color }}">
                                    @if ($call->status === 'answered')
                                        <i class="mdi mdi-phone-check"></i>
                                    @elseif($call->status === 'not_answered')
                                        <i class="mdi mdi-phone-missed"></i>
                                    @elseif($call->status === 'busy')
                                        <i class="mdi mdi-phone-alert"></i>
                                    @else
                                        <i class="mdi mdi-phone-off"></i>
                                    @endif
                                    {{ $call->status_text }}
                                </span>
                            </td>
                            <td>
                                @if ($call->duration)
                                    <span class="badge bg-info-subtle text-info">
                                        <i class="mdi mdi-clock-outline me-1"></i>
                                        {{ $call->formatted_duration }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <div class="text-truncate" style="max-width: 200px;"
                                    title="{{ $call->description }}">
                                    {{ $call->description }}
                                </div>
                            </td>
                            <td class="text-nowrap">
                                <small class="text-muted">
                                    <i class="mdi mdi-calendar-clock me-1"></i>
                                    {{ dateFormat($call->called_at, 'd.m.Y H:i') }}
                                </small>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    @can('student-call-delete')
                                        <button type="button" class="btn btn-sm btn-outline-danger"
                                            onclick="confirmDelete({{ $call->id }})" data-bs-toggle="tooltip"
                                            title="{{ __('translation.calls.delete_call') }}">
                                            <i class="mdi mdi-delete"></i>
                                        </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($calls->hasPages())
            <div class="d-flex justify-content-center mt-3">
                {{ $calls->links() }}
            </div>
        @endif
    @else
        <div class="text-center py-5">
            <div class="mb-3">
                <i class="mdi mdi-phone-off display-4 text-muted"></i>
            </div>
            <h6 class="text-muted">{{ __('translation.calls.no_calls_yet') }}</h6>
            <p class="text-muted mb-3">{{ __('translation.calls.no_calls_description') }}</p>
            @can('student-call-add')
                <button wire:click="toggleAddForm" class="btn btn-primary btn-sm">
                    <i class="mdi mdi-plus me-1"></i>
                    {{ __('translation.calls.add_first_call') }}
                </button>
            @endcan
        </div>
    @endif
</div>

<script>
    function confirmDelete(callId) {
        if (confirm("{!! __('translation.calls.delete_confirm') !!}")) {
            @this.call('deleteCall', callId);
        }
    }

    // Initialize tooltips
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
