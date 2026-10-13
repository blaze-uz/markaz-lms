<div>
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h6 class="mb-1">@lang('translation.students.reminders_title')</h6>
            <small class="text-muted">@lang('translation.students.reminders_hint')</small>
        </div>
        @can('student-edit')
            <button type="button" wire:click="toggleForm" class="btn btn-primary btn-sm">
                <i class="mdi mdi-calendar-clock me-1"></i>
                @lang('translation.students.reminder_add')
            </button>
        @endcan
    </div>

    @if ($showForm)
        @can('student-edit')
            <div class="card mb-4 border-primary">
                <div class="card-header bg-primary-subtle">
                    <h6 class="card-title mb-0 text-primary">
                        <i class="mdi mdi-calendar-plus me-1"></i>
                        @lang('translation.students.reminder_new')
                    </h6>
                </div>
                <div class="card-body">
                    <form wire:submit.prevent="save">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">@lang('translation.students.reminder_date') <span
                                        class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('reminder_date') is-invalid @enderror"
                                    wire:model="reminder_date">
                                @error('reminder_date')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">@lang('translation.students.reminder_time') <span
                                        class="text-danger">*</span></label>
                                <input type="time" class="form-control @error('reminder_time') is-invalid @enderror"
                                    wire:model="reminder_time" step="60">
                                @error('reminder_time')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">@lang('translation.students.reminder_assignee') <span
                                        class="text-danger">*</span></label>
                                <select class="form-select @error('assigned_user_id') is-invalid @enderror"
                                    wire:model="assigned_user_id">
                                    <option value="">@lang('translation.students.reminder_select_staff')</option>
                                    @foreach ($staffUsers as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                                @error('assigned_user_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">@lang('translation.students.reminder_note')</label>
                                <textarea class="form-control @error('reminder_note') is-invalid @enderror" rows="3"
                                    wire:model="reminder_note" placeholder="@lang('translation.students.reminder_note_placeholder')"></textarea>
                                @error('reminder_note')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="mt-3 d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-sm" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="save">@lang('translation.btn.save')</span>
                                <span wire:loading wire:target="save">@lang('translation.processing')</span>
                            </button>
                            <button type="button" class="btn btn-light btn-sm" wire:click="toggleForm">
                                @lang('translation.btn.cancel')
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endcan
    @endif

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>@lang('translation.students.reminder_datetime')</th>
                    <th>@lang('translation.students.reminder_assignee')</th>
                    <th>@lang('translation.students.reminder_note')</th>
                    <th>@lang('translation.students.reminder_status_column')</th>
                    @can('student-edit')
                        <th class="text-end">@lang('translation.actions')</th>
                    @endcan
                </tr>
            </thead>
            <tbody>
                @forelse ($reminders as $reminder)
                    <tr wire:key="student-reminder-{{ $reminder->id }}">
                        <td class="text-nowrap">
                            {{ dateFormat($reminder->reminder_date, 'd.m.Y') }}
                            {{ dateFormat($reminder->reminder_time, 'H:i') }}
                        </td>
                        <td>{{ $reminder->assignedUser->name ?? '—' }}</td>
                        <td>
                            <span class="text-muted">{{ $reminder->reminder_note ?: '—' }}</span>
                        </td>
                        <td>
                            @if ($reminder->reminder_status === 'completed')
                                <span class="badge bg-success-subtle text-success">@lang('translation.students.reminder_status_completed')</span>
                            @else
                                <span class="badge bg-warning-subtle text-warning">@lang('translation.students.reminder_status_pending')</span>
                            @endif
                        </td>
                        @can('student-edit')
                            <td class="text-end text-nowrap">
                                @if ($reminder->reminder_status !== 'completed')
                                    <button type="button" class="btn btn-sm btn-soft-success"
                                        wire:click="markCompleted({{ $reminder->id }})"
                                        wire:confirm="{{ __('translation.students.reminder_confirm_complete') }}">
                                        <i class="mdi mdi-check"></i>
                                    </button>
                                @endif
                                <button type="button" class="btn btn-sm btn-soft-danger"
                                    wire:click="delete({{ $reminder->id }})"
                                    wire:confirm="{{ __('translation.students.reminder_confirm_delete') }}">
                                    <i class="mdi mdi-delete-outline"></i>
                                </button>
                            </td>
                        @endcan
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">
                            @lang('translation.students.reminders_empty')
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
