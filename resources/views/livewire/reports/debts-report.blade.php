<div>
    @push('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .select2-selection__clear {
            position: absolute !important;
            right: 20px !important;
            top: 50% !important;
            transform: translateY(-50%) !important;
        }
    </style>
    @endpush
    <div>
        <!-- Summary Statistics Cards -->
        <div class="mb-4 row g-3">
            <div class="col-md-4">
                <div class="border-0 shadow-sm card h-100">
                    <div class="card-body">
                        <h6 class="mb-3 card-title text-muted">@lang('translation.total_debts')</h6>
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="mb-0">{{ money($totalAmount) }}</h3>
                                <small class="text-muted">{{ $totalDebts }} @lang('translation.debtors')</small>
                            </div>
                            <div class="p-3 icon-shape bg-light-primary text-primary rounded-3">
                                <i class="fas fa-money-bill-wave fa-fw fa-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="border-0 shadow-sm card h-100">
                    <div class="card-body">
                        <h6 class="mb-3 card-title text-muted">@lang('translation.active_debts')</h6>
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="mb-0">{{ money($activeAmount) }}</h3>
                                <small class="text-muted">{{ $activeDebts }} @lang('translation.debtors')</small>
                            </div>
                            <div class="p-3 icon-shape bg-light-success text-success rounded-3">
                                <i class="fas fa-user-check fa-fw fa-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="border-0 shadow-sm card h-100">
                    <div class="card-body">
                        <h6 class="mb-3 card-title text-muted">@lang('translation.deleted_student_debts')</h6>
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="mb-0">{{ money($deletedAmount) }}</h3>
                                <small class="text-muted">{{ $deletedStudentDebts }}
                                    @lang('translation.debtors')</small>
                            </div>
                            <div class="p-3 icon-shape bg-light-danger text-danger rounded-3">
                                <i class="fas fa-user-slash fa-fw fa-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters and Table Card -->
        <div class="border-0 shadow-sm card">
            <div class="pt-4 pb-3 bg-transparent border-0 card-header">
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="search-box">
                            <div class="position-relative">
                                <input type="text" class="form-control" wire:model.live.debounce.1000ms="search"
                                    placeholder="@lang('translation.search')...">
                                <i
                                    class="fas fa-search position-absolute top-50 end-0 translate-middle-y me-2 text-muted"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3" wire:ignore>
                        <select class="form-select select2" wire:model.live="selectedGroup" id="group-select">
                            <option value="">@lang('translation.all_groups')</option>
                            @foreach($groups as $group)
                            <option value="{{ $group->id }}">{{ $group->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3" wire:ignore>
                        <select class="form-select select2" wire:model.live="selectedTeacher" id="teacher-select">
                            <option value="">@lang('translation.all_teachers')</option>
                            @foreach($teachers as $teacher)
                            <option value="{{ $teacher['id'] }}">{{ $teacher['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2" wire:ignore>
                        <select class="form-select select2" wire:model.live="selectedRange" id="range-select">
                            <option value="">@lang('translation.all_ranges')</option>
                            @foreach($ranges as $range)
                            <option value="{{ $range }}">{{ $range }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="gap-2 col-md-1 d-flex">
                        <button type="button" class="btn btn-light" wire:click="resetFilters"
                            title="@lang('translation.reset_filters')">
                            <i class="fas fa-redo"></i>
                        </button>
                        <div class="dropdown">
                            <button class="btn btn-primary" type="button" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                <i class="fas fa-download me-1"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <button class="dropdown-item" wire:click="export('xlsx')"
                                        wire:loading.attr="disabled">
                                        <i class="fas fa-file-excel me-1"></i> Excel
                                    </button>
                                </li>
                                <li>
                                    <button class="dropdown-item" wire:click="export('csv')"
                                        wire:loading.attr="disabled">
                                        <i class="fas fa-file-csv me-1"></i> CSV
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="mt-3 d-flex justify-content-end">
                    <button type="button" class="btn btn-primary" wire:click="calculateDebts" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="calculateDebts">
                            <i class="fas fa-calculator me-1"></i>
                            @lang('translation.calculate_debts')
                        </span>
                        <span wire:loading wire:target="calculateDebts">
                            <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                            @lang('translation.wait_for_a_while')
                        </span>
                    </button>
                </div>
            </div>
            <div class="p-0 card-body">
                <div class="table-responsive">
                    <table class="table mb-0 table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>@lang('translation.student')</th>
                                <th>@lang('translation.phone')</th>
                                <th>@lang('translation.group')</th>
                                <th>@lang('translation.range')</th>
                                <th class="text-end">@lang('translation.amount')</th>
                                <th class="text-end">@lang('translation.left_amount')</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody wire:loading.class="opacity-50">
                            @forelse($debts as $debt)
                            <tr>
                                <td class="text-muted">#{{ $debt->id }}</td>
                                <td>
                                    @if($debt->student)
                                    <a href="{{ route('admin.students.show', $debt->student_id) }}" class="text-body">
                                        {{ $debt->name }} {{ $debt->surname }}
                                        @if($debt->student->deleted_at)
                                        <span class="badge bg-danger">@lang('translation.deleted')</span>
                                        @endif
                                    </a>
                                    @else
                                    <span class="text-muted">@lang('translation.student_not_found')</span>
                                    @endif
                                </td>
                                <td class="text-nowrap">
                                    @if($debt->student)
                                    {{ $debt->student->getFormattedPhone() }}
                                    @else
                                    <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($debt->group)
                                    <a href="{{ route('admin.groups.show', $debt->group_id) }}" class="text-body">
                                        {{ $debt->group->name }}
                                    </a>
                                    @else
                                    <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-nowrap">{{ $debt->range ?? '-' }}</td>
                                <td class="text-end">{{ money($debt->amount) }}</td>
                                <td class="text-end">{{ money($debt->left_amount) }}</td>
                                <td class="text-end">
                                    @if(!$debt->student || ($debt->student && $debt->student->deleted_at))
                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                        wire:click="confirmDelete({{ $debt->id }})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="py-4 text-center text-muted">
                                    <i class="mb-2 fas fa-inbox fa-2x"></i>
                                    <p class="mb-0">@lang('translation.no_data')</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="bg-transparent card-footer">
                <div class="mt-4 d-flex justify-content-between align-items-center">
                    <div class="gap-2 d-flex align-items-center">
                        <label class="text-muted">@lang('translation.per_page')</label>
                        <select wire:model.live="perPage" class="form-select form-select-sm" style="width: 70px;">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                    <div>
                        {{ $debts->links() }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true"
            wire:ignore.self>
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteModalLabel">@lang('translation.confirm-delete')</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        @if($debtToDelete)
                        <p>@lang('translation.delete_debt_confirmation')</p>
                        <div class="alert alert-info">
                            <p class="mb-1">
                                <strong>@lang('translation.student'):</strong>
                                @if($debtToDelete->student)
                                {{ $debtToDelete->student->getFullName() }}
                                @if($debtToDelete->student->deleted_at)
                                <span class="badge bg-danger">@lang('translation.deleted')</span>
                                @endif
                                @else
                                <span class="text-muted">@lang('translation.student_not_found')</span>
                                @endif
                            </p>
                            <p class="mb-1">
                                <strong>@lang('translation.amount'):</strong>
                                {{ money($debtToDelete->amount) }}
                            </p>
                            <p class="mb-0">
                                <strong>@lang('translation.left_amount'):</strong>
                                {{ money($debtToDelete->left_amount) }}
                            </p>
                        </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                            @lang('translation.cancel')
                        </button>
                        <button type="button" class="btn btn-danger" wire:click="deleteDebt">
                            @lang('translation.delete')
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('custom-scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            initSelect2();

            document.addEventListener('livewire:navigated', () => {
                initSelect2();
            });

            document.addEventListener('livewire:initialized', () => {
                initSelect2();
            });
        });

        function initSelect2() {
            // Initialize group select
            $('#group-select').select2({
                placeholder: "@lang('translation.select_group')",
                allowClear: true,
                width: '100%',
            }).on('select2:select', function (e) {
                @this.set('selectedGroup', e.target.value);
            }).on('select2:clear', function (e) {
                @this.set('selectedGroup', '');
            });

            // Initialize teacher select
            $('#teacher-select').select2({
                placeholder: "@lang('translation.select_teacher')",
                allowClear: true,
                width: '100%',
            }).on('select2:select', function (e) {
                @this.set('selectedTeacher', e.target.value);
            }).on('select2:clear', function (e) {
                @this.set('selectedTeacher', '');
            });

            // Initialize range select
            $('#range-select').select2({
                placeholder: "@lang('translation.select_range')",
                allowClear: true,
                width: '100%',
            }).on('select2:select', function (e) {
                @this.set('selectedRange', e.target.value);
            }).on('select2:clear', function (e) {
                @this.set('selectedRange', '');
            });

            // Set initial values if they exist
            if (@this.selectedGroup) {
                $('#group-select').val(@this.selectedGroup).trigger('change');
            }
            if (@this.selectedTeacher) {
                $('#teacher-select').val(@this.selectedTeacher).trigger('change');
            }
            if (@this.selectedRange) {
                $('#range-select').val(@this.selectedRange).trigger('change');
            }
        }
    </script>

    <script>
        document.addEventListener('livewire:initialized', () => {
            @this.on('showDeleteModal', () => {
                let modal = new bootstrap.Modal(document.getElementById('deleteModal'));
                modal.show();
            });

            @this.on('hideDeleteModal', () => {
                let modal = bootstrap.Modal.getInstance(document.getElementById('deleteModal'));
                if (modal) {
                    modal.hide();
                }
            });

            // Show notification when calculation starts
            @this.on('calculating-started', (event) => {
                window.notifyMessage(event.message || '{{ __("translation.calculating_started_please_wait") }}', 'info');
            });

            // Show success message when debts are calculated
            @this.on('debts-calculated', (event) => {
                window.notifyMessage(event.message || '{{ __("translation.debts_calculated_successfully") }}', 'success');
            });
        });
    </script>
    @endpush
</div>