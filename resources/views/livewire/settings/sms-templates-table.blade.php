<div>
    <div class="card rounded-4">
        <div class="card-header rounded-top-4">
            <!-- Desktop Header -->
            <div class="d-none d-lg-flex flex-wrap gap-3 justify-content-between align-items-center">
                <div class="mb-0 card-title">
                    @lang('translation.sms.templates')
                </div>
                <div class="flex-wrap gap-2 d-flex">
                    <div class="input-group" style="width: auto;">
                        <span class="input-group-text">
                            <i class="fas fa-filter"></i>
                        </span>
                        <select class="form-select" wire:model.live="selectedStatus">
                            <option value="">@lang('translation.all_statuses')</option>
                            @foreach ($statuses as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="input-group" style="width: auto;">
                        <span class="input-group-text">
                            <i class="fas fa-search"></i>
                        </span>
                        <input class="form-control" type="search" placeholder="{{ __('translation.search') }}"
                            aria-label="{{ __('translation.search') }}" wire:model.live.debounce.500ms="search">
                    </div>
                    <button type="button" class="btn btn-info me-2" data-bs-toggle="modal"
                        data-bs-target="#instructionModal">
                        <i class="fas fa-info-circle me-1"></i>
                        @lang('translation.show_instructions')
                    </button>
                    <a href="{{ route('admin.settings.sms.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>
                        @lang('attr.add')
                    </a>
                </div>
            </div>

            <!-- Mobile Header -->
            <div class="d-lg-none">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">@lang('translation.sms.templates')</h5>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal"
                            data-bs-target="#instructionModal">
                            <i class="fas fa-info-circle"></i>
                        </button>
                        <a href="{{ route('admin.settings.sms.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i>
                        </a>
                    </div>
                </div>

                <!-- Mobile Filters -->
                <div class="row g-2">
                    <div class="col-12">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-search"></i>
                            </span>
                            <input class="form-control" type="search" placeholder="{{ __('translation.search') }}"
                                aria-label="{{ __('translation.search') }}" wire:model.live.debounce.500ms="search">
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-filter"></i>
                            </span>
                            <select class="form-select" wire:model.live="selectedStatus">
                                <option value="">@lang('translation.all_statuses')</option>
                                @foreach ($statuses as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            @if ($templates->count())
                <!-- Desktop Table -->
                <div class="d-none d-lg-block table-responsive">
                    <table class="table align-middle table-hover">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 60px;">ID</th>
                                <th>@lang('translation.sms.template')</th>
                                <th>@lang('translation.sms.status')</th>
                                <th style="width: 120px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($templates as $template)
                                <tr>
                                    <td class="text-center">{{ $template->id }}</td>
                                    <td>
                                        <a href="{{ route('admin.settings.sms.show', $template->id) }}"
                                            class="text-reset">
                                            <div class="fw-medium">{{ $template->name }}</div>
                                            <div class="text-muted small">{{ Str::limit($template->content, 50) }}
                                            </div>
                                        </a>
                                    </td>
                                    <td>
                                        @php
                                            $statusColors = [
                                                'not_registered' => 'warning',
                                                'moderation' => 'info',
                                                'registered' => 'success',
                                            ];
                                            $statusColor = $statusColors[$template->status] ?? 'secondary';
                                        @endphp
                                        <span
                                            class="badge badge-subtle bg-{{ $statusColor }}-subtle text-{{ $statusColor }}">
                                            {{ __('translation.sms.status_' . $template->status) }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <div class="dropdown">
                                            <button class="btn btn-sm" data-bs-toggle="dropdown">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end">
                                                <a href="{{ route('admin.settings.sms.show', $template) }}"
                                                    class="dropdown-item">
                                                    <i class="far fa-eye me-2"></i>
                                                    @lang('translation.sms.show_template')
                                                </a>
                                                @if ($template->status !== 'moderation')
                                                    <a href="{{ route('admin.settings.sms.edit', $template) }}"
                                                        class="dropdown-item">
                                                        <i class="far fa-edit me-2"></i>
                                                        @lang('translation.sms.edit')
                                                    </a>
                                                @endif

                                                @if ($template->status === 'not_registered')
                                                    <button type="button" class="dropdown-item"
                                                        data-template-id="{{ $template->id }}"
                                                        data-action="moderation-confirmation">
                                                        <i class="fas fa-paper-plane me-2"></i>
                                                        @lang('translation.sms.send_to_moderation')
                                                    </button>
                                                @endif

                                                <div class="dropdown-divider"></div>
                                                <form action="{{ route('admin.settings.sms.destroy', $template) }}"
                                                    method="POST" onsubmit="return confirm('@lang('translation.are_you_sure')')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger">
                                                        <i class="far fa-trash-alt me-2"></i>
                                                        @lang('translation.sms.delete')
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Cards -->
                <div class="d-lg-none">
                    @foreach ($templates as $template)
                        <div class="card mb-3 border-0 bg-light">
                            <div class="card-body p-3">
                                <!-- Template Header -->
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div class="flex-grow-1">
                                        <h6 class="card-title mb-1 fw-medium">
                                            <a href="{{ route('admin.settings.sms.show', $template->id) }}"
                                                class="text-decoration-none text-dark">
                                                {{ $template->name }}
                                            </a>
                                        </h6>
                                        <small class="text-muted">ID: {{ $template->id }}</small>
                                    </div>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-end">
                                            <a href="{{ route('admin.settings.sms.show', $template) }}"
                                                class="dropdown-item">
                                                <i class="far fa-eye me-2"></i>
                                                @lang('translation.sms.show_template')
                                            </a>
                                            @if ($template->status !== 'moderation')
                                                <a href="{{ route('admin.settings.sms.edit', $template) }}"
                                                    class="dropdown-item">
                                                    <i class="far fa-edit me-2"></i>
                                                    @lang('translation.sms.edit')
                                                </a>
                                            @endif

                                            @if ($template->status === 'not_registered')
                                                <button type="button" class="dropdown-item"
                                                    data-template-id="{{ $template->id }}"
                                                    data-action="moderation-confirmation">
                                                    <i class="fas fa-paper-plane me-2"></i>
                                                    @lang('translation.sms.send_to_moderation')
                                                </button>
                                            @endif

                                            <div class="dropdown-divider"></div>
                                            <form action="{{ route('admin.settings.sms.destroy', $template) }}"
                                                method="POST" onsubmit="return confirm('@lang('translation.are_you_sure')')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger">
                                                    <i class="far fa-trash-alt me-2"></i>
                                                    @lang('translation.sms.delete')
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <!-- Template Content -->
                                <p class="card-text text-muted small mb-3">
                                    {{ Str::limit($template->content, 80) }}
                                </p>

                                <!-- Template Meta -->
                                <div class="d-flex flex-wrap gap-2 align-items-center">
                                    @php
                                        $statusColors = [
                                            'not_registered' => 'warning',
                                            'moderation' => 'info',
                                            'registered' => 'success',
                                        ];
                                        $statusColor = $statusColors[$template->status] ?? 'secondary';
                                    @endphp
                                    <span
                                        class="badge badge-subtle bg-{{ $statusColor }}-subtle text-{{ $statusColor }}">
                                        {{ __('translation.sms.status_' . $template->status) }}
                                    </span>
                                </div>

                                <!-- Quick Actions -->
                                <div class="d-flex gap-2 mt-3">
                                    <a href="{{ route('admin.settings.sms.show', $template->id) }}"
                                        class="btn btn-outline-primary btn-sm flex-fill">
                                        <i class="far fa-eye me-1"></i>
                                        @lang('translation.sms.show_template')
                                    </a>
                                    @if ($template->status !== 'moderation')
                                        <a href="{{ route('admin.settings.sms.edit', $template) }}"
                                            class="btn btn-outline-success btn-sm flex-fill">
                                            <i class="far fa-edit me-1"></i>
                                            @lang('translation.sms.edit')
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-3">
                    {{ $templates->links() }}
                </div>
            @else
                <x-no-result />
            @endif
        </div>
        <div class="card-preloader d-none" wire:loading.class.remove="d-none">
            <div class="card-status">
                <div class="spinner-border text-success">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        </div>
    </div>

</div>
