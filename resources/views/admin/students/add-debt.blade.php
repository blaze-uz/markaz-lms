@extends('layouts.master')

@section('title')
    @lang('translation.students.add_debt')
@stop

@section('content')
    {{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif


    <!-- Student Info Section at Top -->
    <div class="row justify-content-center">
        <div class="col-lg-8 col-xl-7">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                        <div class="d-flex align-items-center gap-3">
                            {!! Avatar::create($item->getFullName())->setTheme('grayscale-light')->setDimension(50)->setFontSize(20)->toSvg() !!}
                            <div>
                                <h5 class="mb-1">
                                    <a href="{{ route('admin.students.show', $item->id) }}" class="text-reset">
                                        {{ $item->getFullName() }}
                                    </a>
                                </h5>
                                <div class="d-flex flex-wrap gap-3 text-muted small">
                                    <span>
                                        <i class="mdi mdi-phone me-1"></i>
                                        <a href="tel:{{ $item->phone }}" class="text-reset">
                                            {{ $item->getFormattedPhone() }}
                                        </a>
                                    </span>
                                    @if ($item->parent_phone)
                                        <span>
                                            <i class="mdi mdi-account-multiple me-1"></i>
                                            <a href="tel:{{ $item->parent_phone }}" class="text-reset">
                                                {{ $item->getFormattedPhone(true) }}
                                            </a>
                                        </span>
                                    @endif
                                    <span>
                                        <i class="mdi mdi-wallet me-1"></i>
                                        <span @class([
                                            'fw-bold',
                                            'text-danger' => $item->account?->getBalance() < 0,
                                            'text-success' => $item->account?->getBalance() > 0,
                                            'text-muted' => $item->account?->getBalance() == 0,
                                        ])>
                                            {{ money($item->account?->getBalance()) }}
                                        </span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Form Card -->
    <div class="row justify-content-center">
        <div class="col-lg-8 col-xl-7">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-transparent border-0 pb-0 pt-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="mb-0">@lang('translation.students.add_debt')</h4>
                        <button type="button" class="btn btn-soft-info btn-sm" data-bs-toggle="modal"
                            data-bs-target="#instructionModal">
                            <i class="ri-question-line me-1"></i>
                            @lang('translation.instruction')
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.students.add-debt', $item->id) }}" method="POST">
                        @csrf

                        @if ($errors->any())
                            <div class="alert alert-danger rounded-3">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="mb-3">
                            <label for="group_id" class="form-label fw-medium">
                                @lang('translation.groups.group')
                                <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('group_id') is-invalid @enderror" id="group_id"
                                name="group_id" required>
                                <option value="">{{ __('translation.choose') }}</option>
                                @foreach ($groups as $group)
                                    <option value="{{ $group->id }}" data-payment-type="{{ $group->payment_type }}"
                                        {{ old('group_id') == $group->id ? 'selected' : '' }}>
                                        {{ $group->name }}
                                        @if ($group->teacher)
                                            - {{ $group->teacher->name }}
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('group_id')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- Group Info Section -->
                        <div class="mb-3" id="group-info-container" style="display: none;">
                            <div class="card border border-info-subtle bg-info-subtle rounded-3">
                                <div class="card-body">
                                    <h6 class="mb-3 fw-bold text-info">
                                        <i class="ri-information-line me-2"></i>
                                        @lang('translation.students.group_info')
                                    </h6>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <small class="text-muted d-block">@lang('translation.users.teacher')</small>
                                            <span class="fw-medium" id="group-teacher">-</span>
                                        </div>
                                        <div class="col-md-6">
                                            <small class="text-muted d-block">@lang('translation.courses.course')</small>
                                            <span class="fw-medium" id="group-course">-</span>
                                        </div>
                                        <div class="col-md-6">
                                            <small class="text-muted d-block">@lang('translation.students.payment_amount')</small>
                                            <span class="fw-medium text-primary" id="group-price">-</span>
                                        </div>
                                        <div class="col-md-6">
                                            <small class="text-muted d-block">@lang('translation.students.payment_type')</small>
                                            <span class="fw-medium" id="group-payment-type">-</span>
                                        </div>
                                        <div class="col-md-6">
                                            <small class="text-muted d-block">@lang('attr.status')</small>
                                            <span class="badge" id="group-state">-</span>
                                        </div>
                                        <div class="col-md-6">
                                            <small class="text-muted d-block">@lang('translation.students.students_count')</small>
                                            <span class="fw-medium" id="group-students-count">-</span>
                                        </div>
                                        <div class="col-md-6">
                                            <small class="text-muted d-block">@lang('translation.groups.lesson_count')</small>
                                            <span class="fw-medium" id="group-lesson-count">-</span>
                                        </div>
                                        <div class="col-md-6">
                                            <small class="text-muted d-block">@lang('translation.groups.start_date')</small>
                                            <span class="fw-medium" id="group-start-date">-</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3" id="range-container" style="display: none;">
                            <label for="range" class="form-label fw-medium">
                                @lang('translation.groups.debt_range')
                                <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('range') is-invalid @enderror" id="range" name="range"
                                required>
                                <option value="">{{ __('translation.choose') }}</option>
                            </select>
                            @error('range')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                            <small class="text-muted">
                                <i class="ri-information-line me-1"></i>
                                @lang('translation.students.debt_range_will_show')
                            </small>
                        </div>

                        <div class="mb-4">
                            <x-money-input :label="__('translation.students.debt_amount')" name="amount" :placeholder="__('translation.students.debt_amount')" :value="old('amount') ?? ''" />
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.students.show', $item->id) }}" class="btn btn-light">
                                @lang('translation.btn.cancel')
                            </a>
                            <button type="button" id="submit-btn" class="btn btn-danger">
                                <i class="mdi mdi-account-alert me-1"></i>
                                @lang('translation.students.add_debt')
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content rounded-4">
                <div class="modal-header rounded-top-4 bg-warning-subtle">
                    <h5 class="modal-title" id="confirmModalLabel">
                        <i class="ri-questionnaire-line me-2"></i>
                        @lang('translation.students.confirm_add_debt')
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info border-0 rounded-3 mb-3">
                        <i class="ri-information-line me-2"></i>
                        @lang('translation.students.please_review_debt_info')
                    </div>

                    <div class="card border-0 bg-light">
                        <div class="card-body">
                            <h6 class="fw-bold mb-3">@lang('translation.students.info')</h6>
                            <table class="table table-borderless mb-0">
                                <tr>
                                    <th class="text-muted" style="width: 150px;">@lang('translation.students.student'):</th>
                                    <td><strong>{{ $item->getFullName() }}</strong></td>
                                </tr>
                                <tr>
                                    <th class="text-muted">@lang('translation.students.phone'):</th>
                                    <td>{{ $item->getFormattedPhone() }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="card border-0 bg-light mt-3">
                        <div class="card-body">
                            <h6 class="fw-bold mb-3">@lang('translation.students.debt_details')</h6>
                            <table class="table table-borderless mb-0">
                                <tr>
                                    <th class="text-muted text-nowrap">@lang('translation.groups.group'):</th>
                                    <td><strong id="confirm-group-name">-</strong></td>
                                </tr>
                                <tr>
                                    <th class="text-muted">@lang('translation.groups.debt_range'):</th>
                                    <td><strong id="confirm-debt-range">-</strong></td>
                                </tr>
                                <tr>
                                    <th class="text-muted text-nowrap">@lang('translation.students.debt_amount'):</th>
                                    <td><strong class="text-danger" id="confirm-debt-amount">-</strong></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                        @lang('translation.btn.cancel')
                    </button>
                    <button type="button" id="confirm-submit-btn" class="btn btn-danger">
                        <i class="mdi mdi-check me-1"></i>
                        @lang('translation.students.confirm_and_add_debt')
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Instruction Modal -->
    <div class="modal fade" id="instructionModal" tabindex="-1" aria-labelledby="instructionModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content rounded-4">
                <div class="modal-header rounded-top-4 bg-info-subtle">
                    <h5 class="modal-title" id="instructionModalLabel">
                        <i class="ri-information-line me-2"></i>
                        @lang('translation.students.add_debt_instructions')
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-4">
                        <h6 class="fw-bold text-primary mb-3">
                            <i class="ri-lightbulb-line me-2"></i>
                            @lang('translation.students.steps')
                        </h6>
                        <ul class="list-group">
                            <li class="list-group-item border-0 ps-0">
                                <strong>@lang('translation.students.step_1'):</strong>
                                @lang('translation.students.select_student_group')
                            </li>
                            <li class="list-group-item border-0 ps-0">
                                <strong>@lang('translation.students.step_2'):</strong>
                                @lang('translation.students.select_debt_range_for_group')
                            </li>
                            <li class="list-group-item border-0 ps-0">
                                <strong>@lang('translation.students.step_3'):</strong>
                                @lang('translation.students.enter_debt_amount')
                            </li>
                            <li class="list-group-item border-0 ps-0">
                                <strong>@lang('translation.students.step_4'):</strong>
                                @lang('translation.students.click_add_debt_button')
                            </li>
                        </ul>
                    </div>

                    <div class="alert alert-info border-0 rounded-3">
                        <h6 class="alert-heading mb-2">
                            <i class="ri-alert-line me-2"></i>
                            @lang('translation.students.important_notes')
                        </h6>
                        <ul class="mb-0">
                            <li>@lang('translation.students.debt_range_depends_on_payment_type')</li>
                            <li>@lang('translation.students.debt_amount_must_be_positive')</li>
                            <li>@lang('translation.students.debt_will_appear_in_debts_section')</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">
                        <i class="ri-check-line me-1"></i>
                        @lang('translation.understood')
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="{{ asset('assets/js/jquery-3.7.1.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            // Helper function to format money
            function formatMoney(amount) {
                if (!amount) return '-';
                return new Intl.NumberFormat('uz-UZ', {
                    style: 'currency',
                    currency: 'UZS',
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 0
                }).format(amount).replace('UZS', 'so\'m');
            }

            $('#group_id').on('change', function() {
                const groupId = $(this).val();
                const rangeContainer = $('#range-container');
                const rangeSelect = $('#range');
                const groupInfoContainer = $('#group-info-container');

                if (!groupId) {
                    rangeContainer.hide();
                    groupInfoContainer.hide();
                    rangeSelect.html('<option value="">{{ __('translation.choose') }}</option>');
                    return;
                }

                // Fetch group info
                const groupInfoUrl = "{{ route('admin.students.get-group-info', ':id') }}".replace(':id',
                    groupId);
                $.ajax({
                    url: groupInfoUrl,
                    type: 'GET',
                    success: function(groupInfo) {
                        // Update group info display
                        $('#group-teacher').text(groupInfo.teacher || '-');
                        $('#group-course').text(groupInfo.course || '-');
                        $('#group-price').text(formatMoney(groupInfo.price));

                        // Payment type
                        const paymentTypeText = groupInfo.payment_type === 'date_to_date' ?
                            '{{ __('translation.groups.date_to_date') }}' : (groupInfo
                                .payment_type === 'by_count_lessons' ?
                                '{{ __('translation.groups.by_count_lessons') }}' : '-');
                        $('#group-payment-type').text(paymentTypeText);

                        // State badge
                        const stateBadge = $('#group-state');
                        const stateText = groupInfo.state === 'active' ?
                            '{{ __('translation.groups.active') }}' :
                            (groupInfo.state === 'demo' ?
                                '{{ __('translation.groups.demo') }}' :
                                (groupInfo.state === 'finished' ?
                                    '{{ __('translation.groups.finished') }}' : groupInfo
                                    .state || '-'));
                        const stateClass = groupInfo.state === 'active' ?
                            'bg-success-subtle text-success' :
                            (groupInfo.state === 'demo' ? 'bg-warning-subtle text-warning' :
                                (groupInfo.state === 'finished' ?
                                    'bg-secondary-subtle text-secondary' : 'bg-light text-dark')
                            );
                        stateBadge.text(stateText).removeClass().addClass('badge ' +
                            stateClass);

                        $('#group-students-count').text(groupInfo.students_count || 0);
                        $('#group-lesson-count').text(groupInfo.lesson_count || '-');
                        $('#group-start-date').text(groupInfo.start_date || '-');

                        groupInfoContainer.show();
                    },
                    error: function(xhr) {
                        console.error('Error fetching group info:', xhr);
                    }
                });

                // Show loading state for ranges
                rangeSelect.html('<option value="">{{ __('translation.loading') }}...</option>');
                rangeContainer.show();

                // Fetch payment ranges for selected group
                const url = "{{ route('admin.students.get-group-ranges', ':id') }}".replace(':id',
                    groupId);

                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(response) {
                        rangeSelect.html(
                            '<option value="">{{ __('translation.choose') }}</option>');

                        if (response && response.length > 0) {
                            response.forEach(function(range) {
                                rangeSelect.append(
                                    $('<option></option>')
                                    .attr('value', range.value)
                                    .text(range.label)
                                );
                            });
                        } else {
                            rangeSelect.html(
                                '<option value="">{{ __('translation.no_data') }}</option>'
                            );
                        }
                    },
                    error: function(xhr) {
                        console.error('Error fetching ranges:', xhr);
                        rangeSelect.html(
                            '<option value="">{{ __('translation.error_occurred') }}</option>'
                        );
                    }
                });
            });

            // Trigger change if group is pre-selected (from old input)
            @if (old('group_id'))
                $('#group_id').trigger('change');
                @if (old('range'))
                    setTimeout(function() {
                        $('#range').val('{{ old('range') }}');
                    }, 500);
                @endif
            @endif

            // Handle form submission with confirmation
            $('#submit-btn').on('click', function(e) {
                e.preventDefault();

                // Validate form
                const groupId = $('#group_id').val();
                const range = $('#range').val();
                const amount = $('#amount').val();

                if (!groupId) {
                    alert('{{ __('translation.please_select_group') }}');
                    return;
                }

                if (!$('#range-container').is(':visible') || !range) {
                    alert('{{ __('translation.please_select_debt_range') }}');
                    return;
                }

                if (!amount || parseFloat(amount.replace(/,/g, '')) <= 0) {
                    alert('{{ __('translation.please_enter_debt_amount') }}');
                    return;
                }

                // Get selected values for confirmation
                const groupName = $('#group_id option:selected').text();
                const rangeText = $('#range option:selected').text();
                const formattedAmount = formatMoney(parseFloat(amount.replace(/,/g, '')));

                // Update confirmation modal
                $('#confirm-group-name').text(groupName);
                $('#confirm-debt-range').text(rangeText);
                $('#confirm-debt-amount').text(formattedAmount);

                // Show confirmation modal
                const confirmModal = new bootstrap.Modal(document.getElementById('confirmModal'));
                confirmModal.show();
            });

            // Handle confirmed submission
            $('#confirm-submit-btn').on('click', function() {
                // Hide modal and submit form
                const confirmModal = bootstrap.Modal.getInstance(document.getElementById('confirmModal'));
                confirmModal.hide();

                // Submit the form
                $('form').submit();
            });
        });
    </script>
@endsection
