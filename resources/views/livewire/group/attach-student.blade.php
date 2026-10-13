<div>
    @push('css')
        <link href="{{ asset('assets/libs/flatpickr/flatpickr.min.css') }}" rel="stylesheet" type="text/css">
        <style>
            .info-table {
                width: 100%;
                font-size: 0.9rem;
            }

            .info-table tr {
                border-bottom: 1px solid #f0f0f0;
            }

            .info-table tr:last-child {
                border-bottom: none;
            }

            .info-table td {
                padding: 0.5rem 0;
            }

            .info-table td:first-child {
                color: #6c757d;
                width: 45%;
            }

            .info-table td:last-child {
                font-weight: 600;
                text-align: right;
            }

            .section-card {
                border: 1px solid #e9ecef;
                border-radius: 0.5rem;
                background: #fff;
                margin-bottom: 1rem;
            }

            .section-card-header {
                background: #f8f9fa;
                border-bottom: 1px solid #e9ecef;
                padding: 0.75rem 1rem;
                font-weight: 600;
                font-size: 0.9rem;
                border-radius: 0.5rem 0.5rem 0 0;
            }

            .section-card-body {
                padding: 1rem;
            }

            /* Multi-select balandligini input bilan bir xil qilish */
            .select2-container--default .select2-selection--multiple {
                min-height: 38px !important;
                padding: 3px 8px !important;
            }

            .select2-container--default .select2-selection--multiple .select2-selection__rendered {
                padding: 0 !important;
                margin: 0 !important;
                display: flex;
                align-items: center;
                flex-wrap: wrap;
                gap: 4px;
            }

            .select2-container--default .select2-selection--multiple .select2-selection__choice {
                margin: 0 !important;
                padding: 2px 8px !important;
                font-size: 0.875rem;
                line-height: 1.3;
            }

            .select2-container--default .select2-selection--multiple .select2-search__field {
                margin: 0 !important;
                padding: 0 !important;
                height: 28px !important;
                line-height: 28px !important;
            }

            .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
                font-size: 16px;
                line-height: 1;
            }
        </style>
    @endpush
    <form action="{{ route('admin.groups.attach') }}" method="POST" id="attach-student-form">
        <div class="row g-3">
            @csrf
            <!-- Left Side - Group Info -->
            <div class="col-lg-4">
                <div class="section-card">
                    <div class="section-card-header">
                        @lang('translation.groups.group_info')
                    </div>
                    <div class="section-card-body">
                        @if ($selectedGroup)
                            @if ($selectedGroup->state == 'active')
                                <table class="info-table">
                                        <tr>
                                        <td>@lang('translation.groups.name')</td>
                                        <td>
                                            <a href="{{ route('admin.groups.show', $selectedGroup->id) }}" target="_blank">
                                                {{ $selectedGroup->name }}
                                                <i class="fa fa-arrow-up-right-from-square ms-1" style="font-size: 0.9em;"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @if ($selectedGroup->course)
                                        <tr>
                                            <td>@lang('translation.groups.course')</td>
                                            <td>{{ $selectedGroup->course->name }}</td>
                                        </tr>
                                    @endif
                                    @if ($selectedGroup->teacher)
                                        <tr>
                                            <td>@lang('translation.groups.teacher')</td>
                                            <td>
                                                <a href="{{ route('admin.users.show', $selectedGroup->teacher->id) }}" target="_blank">
                                                    {{ $selectedGroup->teacher->name }}
                                                    <i class="fa fa-arrow-up-right-from-square ms-1" style="font-size: 0.9em;"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endif
                                    @if ($selectedGroup->start_date)
                                        <tr>
                                            <td>@lang('translation.groups.start_date')</td>
                                            <td>{{ $selectedGroup->start_date->format('d.m.Y') }}</td>
                                        </tr>
                                    @endif
                                    <tr>
                                        <td>@lang('translation.groups.students_count')</td>
                                        <td>{{ $selectedGroup->students_count }}</td>
                                    </tr>
                                    <tr>
                                        <td>@lang('translation.groups.amount')</td>
                                        <td class="text-success">{{ number_format($selectedGroup->price, 0, '.', ' ') }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>@lang('translation.groups.monthly_payment_type')</td>
                                        <td>
                                            @if ($selectedGroup->payment_type)
                                                <span class="text-success">
                                                    {{ __('translation.groups.' . $selectedGroup->payment_type) }}
                                                </span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger">
                                                    @lang('translation.groups.not_set')
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>@lang('translation.groups.debt_range')</td>
                                        <td>{{ $selectedGroupDebtRange }}</td>
                                    </tr>
                                    @if ($selectedGroupAmountPerLesson)
                                        <tr>
                                            <td>@lang('translation.groups.amount_per_lesson')</td>
                                            <td>{{ number_format($selectedGroupAmountPerLesson, 0, '.', ' ') }}</td>
                                        </tr>
                                    @endif
                                </table>
                            @else
                                <div class="alert alert-warning border-0 mb-0">
                                    <h6 class="mb-2"><i class="fa-solid fa-flask me-1"></i> @lang('translation.groups.group_is_demo')</h6>
                                    <p class="mb-2 small">@lang('translation.groups.demo_group_description')</p>
                                    @if ($selectedGroup)
                                        <table class="info-table mt-2">
                                            <tr>
                                                <td>@lang('translation.groups.name')</td>
                                                <td>{{ $selectedGroup->name }}</td>
                                            </tr>
                                            @if ($selectedGroup->course)
                                                <tr>
                                                    <td>@lang('translation.groups.course')</td>
                                                    <td>{{ $selectedGroup->course->name }}</td>
                                                </tr>
                                            @endif
                                            @if ($selectedGroup->teacher)
                                                <tr>
                                                    <td>@lang('translation.groups.teacher')</td>
                                                    <td>{{ $selectedGroup->teacher->name }}</td>
                                                </tr>
                                            @endif
                                        </table>
                                    @endif
                                </div>
                            @endif
                        @else
                            <div class="alert alert-primary text-center mb-0">
                                @lang('translation.groups.choose_group')
                            </div>
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

            <!-- Right Side - Form Selection Section -->
            <div class="col-lg-8">
                <!-- Form Selection Section -->
                <div class="section-card">
                    <div class="section-card-header">
                        @lang('translation.groups.choose_group') & @lang('translation.groups.choose_student')
                    </div>
                    <div class="section-card-body">
                        @if ($errors->any())
                            <div class="alert alert-danger alert-sm mb-3">
                                <ul class="mb-0 ps-3">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="group" class="form-label">@lang('translation.groups.choose_group')</label>
                                <div wire:ignore>
                                    <select name="group_id" id="group" data-control="select2"
                                        wire:model="selectedGroupId"
                                        class="form-select group-select @error('group_id') is-invalid @enderror">
                                        <option value="">@lang('translation.groups.choose_group')</option>
                                        @foreach ($groups as $group)
                                            <option value="{{ $group->id }}"
                                                @if ($selectedGroupId == $group->id) selected @endif>
                                                {{ $group->name }} | {{ $group->teacher?->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('group_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12">
                                <label for="student" class="form-label">@lang('translation.groups.choose_student')</label>
                                <div wire:ignore>
                                    <select name="student_ids[]" id="student" multiple
                                        class="students-select form-select @error('student_ids') is-invalid @enderror"
                                        data-placeholder="@lang('translation.groups.choose_student')">
                                        @foreach ($students as $student)
                                            <option value="{{ $student->id }}"
                                                @if ($selectedStudentId == $student->id || in_array($student->id, $selectedStudentsIds)) selected @endif>
                                                {{ $student->getFullName() }} | {{ $student->getFormattedPhone() }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('student_ids')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="mt-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch"
                                    id="editGroupAgreedAmountSwitch" wire:model.live="editGroupAgreedAmount">
                                <label class="form-check-label" for="editGroupAgreedAmountSwitch">
                                    @lang('translation.students.edit_group_agreed_amount')
                                </label>
                            </div>
                        </div>
                        <div class="row g-3 mt-2 @if (!$editGroupAgreedAmount) d-none @endif">
                            <div class="col-md-6">
                                <label class="form-label">@lang('translation.students.payment_amount')</label>
                                <input type="tel" id="group-agreed-amount" name="group_agreed_amount"
                                    class="form-control money @error('group_agreed_amount') is-invalid @enderror"
                                    placeholder="{{ __('translation.students.payment_amount') }}">
                                @error('group_agreed_amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="attach-comment" class="form-label">@lang('translation.groups.comment')</label>
                                <input type="text" id="attach-comment" name="comment"
                                    class="form-control @error('comment') is-invalid @enderror"
                                    placeholder="@lang('translation.groups.comment')">
                                @error('comment')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Calendar Section -->
                <div class="section-card">
                    <div class="section-card-header">
                        Qo'shilish sanasi va to'lov
                    </div>
                    <div class="section-card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div wire:ignore>
                                    <input type="text" class="form-control" id="calendar" data-date-format="d M, Y"
                                        data-inline-date="true">
                                    <input type="hidden" name="attached_at" id="attached_at" value="">
                                </div>
                            </div>
                            <div class="col-md-6">
                                @if ($selectedGroup && $selectedGroup->payment_type === 'one_time')
                                    <div class="alert alert-success border-0 mb-2">
                                        <i class="ri-coins-line me-1"></i>
                                        <strong>@lang('translation.groups.one_time_payment_info')</strong>
                                        <div class="mt-1 small text-muted">@lang('translation.groups.one_time_payment_hint')</div>
                                    </div>
                                    <label for="debt-amount" class="form-label">@lang('translation.students.payment_amount')</label>
                                @else
                                    <label for="debt-amount" class="form-label">@lang('translation.students.edit_first_month_payment_amount')</label>
                                @endif
                                <input type="tel" id="debt-amount" name="first_month_debt_amount"
                                    class="form-control money @error('first_month_debt_amount') is-invalid @enderror"
                                    wire:model="selectedGroupDebtAmount"
                                    placeholder="{{ __('translation.students.edit_first_month_payment_amount') }}">
                                @error('first_month_debt_amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                @if ($firstMonthDebtAmountWarning && (!$selectedGroup || $selectedGroup->payment_type !== 'one_time'))
                                    <div class="alert alert-warning mt-2 py-2 px-3 small">
                                        * @lang('translation.students.first_month_debt_amount_warning')
                                    </div>
                                @endif
                                @if ($showOldPeriodDebtOption && (!$selectedGroup || $selectedGroup->payment_type !== 'one_time'))
                                    <div class="alert alert-info mt-2 py-2 px-3 border-0">
                                        <strong class="d-block mb-1 small">@lang('translation.import_validation.old_period_debts')</strong>
                                        <small class="d-block mb-2">@lang('translation.import_validation.old_period_debts_description')</small>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" role="switch"
                                                id="createOldPeriodDebtsSwitch" name="create_old_period_debts"
                                                value="1" wire:model.live="createOldPeriodDebts">
                                            <label class="form-check-label small" for="createOldPeriodDebtsSwitch">
                                                @lang('translation.import_validation.create_old_period_debts')
                                            </label>
                                        </div>
                                        @if ($createOldPeriodDebts && $oldPeriodsCount > 0)
                                            <small class="text-success d-block mt-2">
                                                <i class="fa-solid fa-check-circle me-1"></i>
                                                {{ $oldPeriodsCount }} @lang('translation.import_validation.old_periods_count')
                                            </small>
                                        @endif
                                    </div>
                                @endif
                                <button type="button" class="btn btn-sm bg-primary-subtle text-primary w-100 mt-2"
                                    data-bs-toggle="modal" data-bs-target="#groupLessonTimesInfoModal">
                                    <i class="fa-solid fa-circle-info me-1"></i>
                                    @lang('translation.groups.group_times_info')
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 mt-0 mb-3">
                <div class="d-flex justify-content-end">
                    <x-button id="detach-btn" class="btn-success">
                        @lang('translation.btn.save')
                    </x-button>
                </div>
            </div>
        </div>
    </form>

    <!-- Group lesson times info -->
    @include('admin.groups.partials.group-lesson-times-info-modal')

    @push('script-bottom')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.15/jquery.mask.js"></script>
        <script src="{{ asset('assets/libs/flatpickr/flatpickr.min.js') }}"></script>
        @script
            <script>
                // Initialize students select2 with proper settings
                $('.students-select').select2({
                    placeholder: "@lang('translation.groups.choose_student')",
                    allowClear: true,
                    width: '100%'
                });

                $('.group-select').on('select2:select', function(e) {
                    let value = $(this).val();
                    $wire.dispatch('select-group', {
                        group_id: value
                    });
                });

                document.addEventListener('select2Hydrate', event => {
                    $('.money').mask('000,000,000,000', {
                        reverse: true
                    });

                    // Re-initialize students select after livewire update
                    $('.students-select').select2({
                        placeholder: "@lang('translation.groups.choose_student')",
                        allowClear: true,
                        width: '100%'
                    });
                });

                flatpickr("#calendar", {
                    inline: true,
                    enableTime: false,
                    dateFormat: "Y-m-d",
                    defaultDate: "today",
                    time_24hr: true,
                    locale: {
                        firstDayOfWeek: 1,
                        weekdays: {
                            shorthand: ['Yak', 'Du', 'Se', 'Chor', 'Pay', 'Ju', 'Sha'],
                            longhand: ['Yakshanba', 'Dushanba', 'Seshanba', 'Chorshanba', 'Payshanba', 'Juma', 'Shanba'],
                        },
                        months: {
                            shorthand: ['Yan', 'Fev', 'Mar', 'Apr', 'May', 'Iyun', 'Iyul', 'Avg', 'Sen', 'Okt', 'Noy',
                                'Dek'
                            ],
                            longhand: ['Yanvar', 'Fevral', 'Mart', 'Aprel', 'May', 'Iyun', 'Iyul', 'Avgust', 'Sentabr',
                                'Oktabr', 'Noyabr', 'Dekabr'
                            ],
                        },
                    },
                    onChange: function(selectedDates, dateStr, instance) {
                        // Update hidden input with selected date
                        document.getElementById('attached_at').value = dateStr;

                        $wire.dispatch('calendar-date-changed', {
                            date: dateStr
                        });
                    },
                    onReady: function(selectedDates, dateStr, instance) {
                        // Set initial value on load
                        document.getElementById('attached_at').value = dateStr;
                    }
                });
            </script>
        @endscript
    @endpush
</div>
