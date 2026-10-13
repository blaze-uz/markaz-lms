@extends('layouts.master')
@section('title')
    @lang('translation.groups.debt_settings')
@endsection

@section('content')
    {{ \Diglactic\Breadcrumbs\Breadcrumbs::render() }}

    <div class="row">
        <!-- Left Side - Start Date Settings -->
        <div class="col-md-4">
            <div class="card shadow-sm rounded-4">
                <div class="card-header rounded-top-4">
                    <div class="px-3 py-2 d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-1">
                                <i class="ri-calendar-line me-2"></i>
                                @lang('translation.groups.start_date')
                            </h5>
                            <p class="mb-0 text-muted small">
                                <i class="ri-group-line me-1"></i>
                                {{ $item->name }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="card-body px-3 py-3">
                    <form action="{{ route('admin.groups.update-start-date', $item->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="start_date" class="form-label">
                                <i class="ri-calendar-line me-1"></i>
                                @lang('translation.groups.start_date')
                            </label>
                            <input type="date" class="form-control @error('start_date') is-invalid @enderror"
                                id="start_date" name="start_date"
                                value="{{ $item->start_date ? $item->start_date->format('Y-m-d') : '' }}" required>
                            @error('start_date')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                            <div class="form-text">
                                <i class="ri-information-line me-1"></i>
                                @lang('translation.groups.start_date_warning')
                            </div>
                        </div>

                        <!-- Old Date Confirmation (Hidden by default) -->
                        <div id="oldDateConfirmation" class="mb-3" style="display: none;">
                            <div class="alert alert-warning">
                                <div class="d-flex align-items-start">
                                    <i class="ri-alert-line me-2 mt-1"></i>
                                    <div class="flex-grow-1">
                                        <h6 class="alert-heading mb-2">@lang('translation.groups.date_changed_warning')</h6>
                                        <p class="mb-2">@lang('translation.groups.date_changed_message')</p>
                                        <div class="alert alert-info py-2 mb-2">
                                            <small>
                                                <i class="ri-information-line me-1"></i>
                                                @lang('translation.groups.debt_info_message')
                                            </small>
                                        </div>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" id="createOldDebts"
                                                name="create_old_debts" value="1">
                                            <label class="form-check-label" for="createOldDebts">
                                                @lang('translation.groups.create_old_debts')
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="deleteCurrentDebts"
                                                name="delete_current_debts" value="1">
                                            <label class="form-check-label" for="deleteCurrentDebts">
                                                @lang('translation.groups.delete_current_debts')
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.groups.show', $item->id) }}" class="btn btn-secondary flex-fill">
                                <i class="ri-arrow-left-line me-1"></i>
                                @lang('translation.btn.back')
                            </a>
                            <button type="submit" class="btn btn-primary flex-fill" id="updateButton">
                                <i class="ri-save-line me-1"></i>
                                @lang('translation.save')
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Billing Frequency Settings -->
            <div class="card shadow-sm rounded-4 mt-3">
                <div class="card-header rounded-top-4">
                    <div class="px-3 py-2">
                        <h5 class="mb-1">
                            <i class="ri-repeat-line me-2"></i>
                            @lang('translation.groups.billing_frequency')
                        </h5>
                        <p class="mb-0 text-muted small">
                            @lang('translation.groups.billing_frequency_hint')
                        </p>
                    </div>
                </div>
                <div class="card-body px-3 py-3">
                    @php
                        $isOneTime = $item->payment_type === 'one_time';
                    @endphp
                    <form action="{{ route('admin.groups.update-billing-frequency', $item->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="d-flex gap-2 mb-3">
                            <div class="flex-fill">
                                <input type="radio" class="btn-check" name="billing_frequency" id="bf-recurring"
                                    value="recurring" @if(! $isOneTime) checked @endif>
                                <label class="btn btn-outline-primary text-nowrap d-flex align-items-center justify-content-center gap-1 w-100"
                                    for="bf-recurring">
                                    <i class="ri-calendar-2-line"></i>
                                    @lang('translation.groups.date_to_date')
                                </label>
                            </div>
                            <div class="flex-fill">
                                <input type="radio" class="btn-check" name="billing_frequency" id="bf-one-time"
                                    value="one_time" @if($isOneTime) checked @endif>
                                <label class="btn btn-outline-success text-nowrap d-flex align-items-center justify-content-center gap-1 w-100"
                                    for="bf-one-time">
                                    <i class="ri-coins-line"></i>
                                    @lang('translation.groups.one_time')
                                </label>
                            </div>
                            @error('billing_frequency')
                                <div class="text-danger w-100">* {{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-text mb-3">
                            <i class="ri-information-line me-1"></i>
                            @lang('translation.groups.one_time_payment_hint')
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="ri-save-line me-1"></i>
                            @lang('translation.save')
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right Side - Debtor Students List -->
        <div class="col-md-8">
            <div class="card shadow-sm rounded-4">
                <div class="card-header rounded-top-4">
                    <div class="px-3 py-2 d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-1">
                                <i class="ri-money-dollar-circle-line me-2"></i>
                                @lang('translation.students.debt_students')
                            </h5>
                            <p class="mb-0 text-muted small">
                                <i class="ri-information-line me-1"></i>
                                @lang('translation.groups.current_debt_range'): {{ $item->getPaymentDate() }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="card-body px-3 py-3">
                    @php
                        $debtManagementService = new \App\Services\DebtManagementService();
                        $debtStudents = $debtManagementService->getDebtorStudents($item);
                    @endphp

                    @if ($debtStudents->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>@lang('translation.students.student')</th>
                                        <th>@lang('attr.phone')</th>
                                        <th>@lang('attr.debt')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($debtStudents as $student)
                                        @php
                                            $debtAmount = $student
                                                ->groupPayments()
                                                ->where('group_id', $item->id)
                                                ->sum('amount');
                                        @endphp
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-xs me-2">
                                                        <span
                                                            class="avatar-title rounded-circle bg-primary-subtle text-primary">
                                                            {{ substr($student->name, 0, 1) }}
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0">{{ $student->name }} {{ $student->surname }}</h6>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="text-muted">{{ $student->getFormattedPhone() }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-danger-subtle text-danger fs-6">
                                                    {{ money($debtAmount) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <div class="avatar-lg mx-auto mb-3">
                                <span class="avatar-title rounded-circle bg-success-subtle text-success">
                                    <i class="ri-check-line fs-2"></i>
                                </span>
                            </div>
                            <h6 class="text-muted">@lang('translation.students.no_debt_students')</h6>
                            <p class="text-muted small mb-0">@lang('translation.students.all_students_paid')</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const startDateInput = document.getElementById('start_date');
            const oldDateConfirmation = document.getElementById('oldDateConfirmation');
            const updateButton = document.getElementById('updateButton');
            const createOldDebtsCheckbox = document.getElementById('createOldDebts');
            const deleteCurrentDebtsCheckbox = document.getElementById('deleteCurrentDebts');

            function checkDate() {
                const selectedDate = new Date(startDateInput.value);
                const currentDate = new Date();
                const originalDate = new Date('{{ $item->start_date ? $item->start_date->format('Y-m-d') : '' }}');

                // Reset confirmation
                oldDateConfirmation.style.display = 'none';
                createOldDebtsCheckbox.checked = false;
                deleteCurrentDebtsCheckbox.checked = false;

                // Check if date has changed from original
                const dateChanged = selectedDate.getTime() !== originalDate.getTime();

                // Check if selected date is in a previous month
                const isOldDate = selectedDate.getFullYear() < currentDate.getFullYear() ||
                    (selectedDate.getFullYear() === currentDate.getFullYear() &&
                        selectedDate.getMonth() < currentDate.getMonth());

                if (dateChanged) {
                    // Show confirmation for any date change
                    oldDateConfirmation.style.display = 'block';

                    // Show/hide create old debts checkbox based on whether it's an old date
                    const createOldDebtsContainer = createOldDebtsCheckbox.closest('.form-check');
                    if (isOldDate) {
                        createOldDebtsContainer.style.display = 'block';
                    } else {
                        createOldDebtsContainer.style.display = 'none';
                    }
                }
            }

            // Check date on input change
            startDateInput.addEventListener('change', checkDate);

            // Check date on page load
            checkDate();

            // Handle form submission
            document.querySelector('form').addEventListener('submit', function(e) {
                const selectedDate = new Date(startDateInput.value);
                const currentDate = new Date();
                const originalDate = new Date(
                    '{{ $item->start_date ? $item->start_date->format('Y-m-d') : '' }}');

                const dateChanged = selectedDate.getTime() !== originalDate.getTime();
                const isOldDate = selectedDate.getFullYear() < currentDate.getFullYear() ||
                    (selectedDate.getFullYear() === currentDate.getFullYear() &&
                        selectedDate.getMonth() < currentDate.getMonth());

                // If date changed and it's an old date but create old debts is not checked, prevent submission
                if (dateChanged && isOldDate && !createOldDebtsCheckbox.checked) {
                    e.preventDefault();
                    alert('@lang('translation.groups.please_confirm_old_debts')');
                    return false;
                }
            });
        });
    </script>
@endsection
