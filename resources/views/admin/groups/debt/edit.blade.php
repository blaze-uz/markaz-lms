@php use Diglactic\Breadcrumbs\Breadcrumbs; @endphp
@extends('layouts.master')
@section('title')
    @lang('translation.groups.edit-debt')
@endsection
@section('content')
    {{ Breadcrumbs::render() }}

    <!-- Student & Group Summary -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card rounded-4 border-0 shadow-sm mb-0">
                <div class="card-body p-3 p-md-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="avatar-sm bg-primary-subtle rounded-3 me-3">
                            <i
                                class="ri-user-line text-primary fs-4 d-flex align-items-center justify-content-center h-100"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="mb-1 fs-6 fs-md-5">{{ $item->student->getFullName() }}</h5>
                            <p class="text-muted mb-0 small">
                                {{ $item->group->name ?? 'Group not found' }} • {{ $item->range }}
                                @if ($item->student->trashed())
                                    <span class="badge bg-danger-subtle text-danger ms-2">
                                        @lang('translation.deleted')
                                    </span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="row g-2 g-md-3">
                        <div class="col-6 col-md-3">
                            <div class="text-center p-2 p-md-3 bg-light rounded-3">
                                <div class="text-muted small">@lang('translation.groups.amount')</div>
                                <div class="fw-semibold fs-6 fs-md-5">{{ money($item->amount) }}</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="text-center p-2 p-md-3 bg-success-subtle rounded-3">
                                <div class="text-muted small">@lang('translation.payments.total_paid')</div>
                                <div class="fw-semibold text-success fs-6 fs-md-5">
                                    {{ money($item->payments->sum('amount')) }}</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="text-center p-2 p-md-3 bg-warning-subtle rounded-3">
                                <div class="text-muted small">@lang('translation.debt_amount')</div>
                                <div class="fw-semibold text-warning fs-6 fs-md-5">{{ money($item->left_amount) }}</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="text-center p-2 p-md-3 bg-info-subtle rounded-3">
                                <div class="text-muted small">@lang('translation.payments.count')</div>
                                <div class="fw-semibold text-info fs-6 fs-md-5">{{ $item->payments->count() }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Cards -->
    <div class="row g-3 g-md-4">
        <!-- Edit Debt Amount -->
        <div class="col-12 col-lg-8">
            <form action="{{ route('admin.groups.update-debt', $item) }}" method="POST" id="edit-debt-form">
                @csrf
                @method('PUT')
                <div class="card rounded-4 border-0 shadow-sm h-100 d-flex flex-column mb-0">
                    <div class="card-header bg-transparent border-0 p-3 p-md-4 pb-0">
                        <h6 class="mb-0 text-dark fs-6 fs-md-5">
                            <i class="ri-edit-line me-2"></i>
                            @lang('translation.groups.edit-debt')
                        </h6>
                    </div>
                    <div class="card-body p-3 pt-0 flex-grow-1 d-flex flex-column">
                        <!-- Contextual Help -->
                        <div class="alert alert-info border-0 bg-info-subtle mb-3">
                            <div class="d-flex">
                                <i class="ri-information-line text-info me-2 mt-1 fs-5"></i>
                                <div>
                                    <h6 class="text-info mb-2 fs-6">@lang('translation.groups.debt_editing_help_title')</h6>
                                    <p class="mb-2 small text-muted">@lang('translation.groups.debt_editing_help_text')</p>
                                    @if ($item->payments->count() > 0)
                                        <p class="mb-0 small">
                                            <strong class="text-info">@lang('translation.groups.minimum_debt_notice'):</strong>
                                            <span class="text-dark">{{ money($item->payments->sum('amount')) }}</span>
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Editable Amount Input -->
                        <div class="mb-3 mb-md-4 flex-grow-1">
                            <label class="form-label text-muted small fw-semibold">@lang('translation.groups.total_debt_amount')</label>
                            <x-money-input :name="'amount'" :label="''" :value="$item->amount" :min="$item->payments->sum('amount')" />
                            @if ($item->payments->count() > 0)
                                <div class="form-text text-muted small">
                                    <i class="ri-shield-check-line me-1"></i>
                                    @lang('translation.groups.debt_cannot_be_less_than_paid', ['amount' => money($item->payments->sum('amount'))])
                                </div>
                            @endif
                        </div>

                        <!-- Action Buttons -->
                        <div class="mt-auto">
                            <div class="d-flex flex-column flex-sm-row gap-2 justify-content-end">
                                <button type="button" class="btn btn-outline-warning" data-bs-toggle="modal"
                                    data-bs-target="#markNoDebtModal">
                                    <i class="ri-check-double-line me-1"></i>
                                    <span class="d-none d-sm-inline">@lang('translation.groups.mark_no_debt_title')</span>
                                    <span class="d-sm-none">@lang('translation.groups.mark_as_no_debt')</span>
                                </button>
                                <button type="submit" class="btn btn-primary" onclick="return confirmSave()">
                                    <i class="ri-save-line me-1"></i>
                                    @lang('translation.btn.save_changes')
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Payments List -->
        <div class="col-12 col-lg-4">
            <div class="card rounded-4 border-0 shadow-sm h-100 d-flex flex-column mb-0">
                <div class="card-header bg-transparent border-0 p-2 p-md-3 pb-0">
                    <h6 class="mb-0 text-dark fs-6 fs-md-5 d-flex align-items-center">
                        <i class="ri-money-dollar-circle-line me-2"></i>
                        @lang('translation.payments.payment_history')
                        <span
                            class="badge bg-primary-subtle text-primary ms-2 small align-middle">{{ $item->payments->count() }}</span>
                    </h6>
                </div>
                <div class="card-body p-2 p-md-3 flex-grow-1 d-flex flex-column">
                    @if ($item->payments->count() > 0)
                        <div class="flex-grow-1">
                            <div class="list-group list-group-flush">
                                @foreach ($item->payments as $payment)
                                    <div class="list-group-item px-0 py-1 py-md-2 border-0 border-bottom">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="flex-grow-1">
                                                <div class="fw-semibold text-success fs-6 fs-md-5">
                                                    {{ money($payment->amount) }}</div>
                                                <small class="text-muted">{{ dateFormat($payment->created_at) }}</small>
                                            </div>
                                            <div class="text-end">
                                                <span
                                                    class="badge bg-success-subtle text-success small">#{{ $payment->id }}</span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="mt-1 mt-md-2 pt-1 pt-md-2 border-top">
                            <div class="d-flex justify-content-between align-items-center">
                                <strong class="text-muted small">@lang('translation.payments.total'):</strong>
                                <strong
                                    class="text-success fs-6 fs-md-5">{{ money($item->payments->sum('amount')) }}</strong>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-2 py-md-3 flex-grow-1 d-flex flex-column justify-content-center">
                            <i class="ri-money-dollar-circle-line text-muted fs-1 mb-1 mb-md-2"></i>
                            <p class="text-muted mb-0 small">@lang('translation.payments.no_payments_yet')</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Mark as No Debt Modal -->
    <div class="modal fade" id="markNoDebtModal" tabindex="-1" aria-labelledby="markNoDebtModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-0 pb-0 p-3 p-md-4">
                    <h5 class="modal-title fs-6 fs-md-5" id="markNoDebtModalLabel">
                        <i class="ri-check-double-line text-warning me-2"></i>
                        @lang('translation.groups.mark_no_debt_title')
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-2 p-3 p-md-4">
                    <div class="alert alert-warning border-0 bg-warning-subtle mb-3">
                        <div class="d-flex">
                            <i class="ri-alert-line text-warning me-2 mt-1"></i>
                            <div>
                                <p class="mb-0 small">
                                    @lang('translation.groups.mark_no_debt_message')
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="row g-3 small text-muted">
                        <div class="col-6">
                            <strong>@lang('translation.groups.current_debt'):</strong><br>
                            <span class="text-warning">{{ money($item->left_amount) }}</span>
                        </div>
                        <div class="col-6">
                            <strong>@lang('translation.groups.paid_amount'):</strong><br>
                            <span class="text-success">{{ money($item->payments->sum('amount')) }}</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 p-3 p-md-4">
                    <div class="d-flex flex-column flex-sm-row gap-2 w-100">
                        <button type="button" class="btn btn-outline-secondary flex-grow-1 flex-sm-grow-0"
                            data-bs-dismiss="modal">
                            <i class="ri-close-line me-1"></i>
                            @lang('translation.groups.cancel_action')
                        </button>
                        <form action="{{ route('admin.groups.mark-no-debt', $item) }}" method="POST"
                            class="flex-grow-1 flex-sm-grow-0">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-warning w-100">
                                <i class="ri-check-double-line me-1"></i>
                                @lang('translation.groups.confirm_action')
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Debt Update History -->
    @if ($activities->count() > 0)
        <div class="row mt-3 mt-md-4">
            <div class="col-12">
                <div class="card rounded-4 border-0 shadow-sm">
                    <div class="card-header bg-transparent border-0 p-3 p-md-4">
                        <div
                            class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
                            <h6 class="mb-0 text-dark d-flex align-items-center fs-6 fs-md-5">
                                <i class="ri-history-line me-2"></i>
                                @lang('translation.students.debt_update_history')
                                <span
                                    class="badge bg-secondary-subtle text-secondary ms-2 small align-middle">{{ $activities->count() }}</span>
                            </h6>
                            <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse"
                                data-bs-target="#historyCollapse" aria-expanded="false">
                                <i class="ri-eye-line me-1"></i>
                                <span class="d-none d-sm-inline">@lang('translation.btn.toggle_history')</span>
                                <span class="d-sm-none">@lang('translation.btn.show')</span>
                            </button>
                        </div>
                    </div>
                    <div class="collapse" id="historyCollapse">
                        <div class="card-body p-3 p-md-4">
                            <!-- Desktop Table View -->
                            <div class="d-none d-md-block">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="border-0 small">ID</th>
                                                <th class="border-0 small">@lang('translation.groups.old_debt_amount')</th>
                                                <th class="border-0 small">@lang('translation.groups.new_debt_amount')</th>
                                                <th class="border-0 small">@lang('translation.students.causer')</th>
                                                <th class="border-0 small">@lang('attr.created_at')</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($activities as $activity)
                                                <tr>
                                                    <td>
                                                        <span
                                                            class="badge bg-light text-dark small">#{{ $activity->id }}</span>
                                                    </td>
                                                    <td>
                                                        @if (isset($activity['properties']['old']))
                                                            @foreach ($activity['properties']['old'] as $key => $value)
                                                                @if ($key == 'left_amount')
                                                                    <span
                                                                        class="text-muted small">{{ money($value) }}</span>
                                                                @endif
                                                            @endforeach
                                                        @else
                                                            <span class="text-muted small">-</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (isset($activity['properties']['attributes']))
                                                            @foreach ($activity['properties']['attributes'] as $key => $value)
                                                                @if ($key == 'left_amount')
                                                                    <span
                                                                        class="fw-semibold text-primary small">{{ money($value) }}</span>
                                                                @endif
                                                            @endforeach
                                                        @else
                                                            <span class="text-muted small">-</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if ($activity->causer)
                                                            <a href="{{ route('admin.users.show', $activity->causer_id) }}"
                                                                class="text-decoration-none">
                                                                <div class="d-flex align-items-center">
                                                                    <div
                                                                        class="avatar-xs bg-primary-subtle rounded-circle me-2">
                                                                        <i
                                                                            class="ri-user-line text-primary fs-6 d-flex align-items-center justify-content-center h-100"></i>
                                                                    </div>
                                                                    <span
                                                                        class="small">{{ $activity->causer->name }}</span>
                                                                </div>
                                                            </a>
                                                        @else
                                                            <span class="text-muted small">@lang('translation.system')</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <small
                                                            class="text-muted">{{ dateFormat($activity->created_at) }}</small>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="text-center py-4">
                                                        <x-no-result />
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Mobile Card View -->
                            <div class="d-md-none">
                                @forelse($activities as $activity)
                                    <div class="card border-0 bg-light-subtle mb-3">
                                        <div class="card-body p-3">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <span class="badge bg-light text-dark small">#{{ $activity->id }}</span>
                                                <small class="text-muted">{{ dateFormat($activity->created_at) }}</small>
                                            </div>

                                            <div class="row g-2 mb-2">
                                                <div class="col-6">
                                                    <div class="small text-muted">@lang('translation.groups.old_debt_amount')</div>
                                                    @if (isset($activity['properties']['old']))
                                                        @foreach ($activity['properties']['old'] as $key => $value)
                                                            @if ($key == 'left_amount')
                                                                <div class="fw-semibold text-muted">{{ money($value) }}
                                                                </div>
                                                            @endif
                                                        @endforeach
                                                    @else
                                                        <div class="fw-semibold text-muted">-</div>
                                                    @endif
                                                </div>
                                                <div class="col-6">
                                                    <div class="small text-muted">@lang('translation.groups.new_debt_amount')</div>
                                                    @if (isset($activity['properties']['attributes']))
                                                        @foreach ($activity['properties']['attributes'] as $key => $value)
                                                            @if ($key == 'left_amount')
                                                                <div class="fw-semibold text-primary">{{ money($value) }}
                                                                </div>
                                                            @endif
                                                        @endforeach
                                                    @else
                                                        <div class="fw-semibold text-muted">-</div>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="d-flex align-items-center">
                                                <div class="small text-muted me-2">@lang('translation.students.causer'):</div>
                                                @if ($activity->causer)
                                                    <a href="{{ route('admin.users.show', $activity->causer_id) }}"
                                                        class="text-decoration-none">
                                                        <div class="d-flex align-items-center">
                                                            <div class="avatar-xs bg-primary-subtle rounded-circle me-2">
                                                                <i
                                                                    class="ri-user-line text-primary fs-6 d-flex align-items-center justify-content-center h-100"></i>
                                                            </div>
                                                            <span class="small">{{ $activity->causer->name }}</span>
                                                        </div>
                                                    </a>
                                                @else
                                                    <span class="text-muted small">@lang('translation.system')</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-4">
                                        <x-no-result />
                                    </div>
                                @endforelse
                            </div>

                            @if ($activities->hasPages())
                                <div class="mt-2 mt-md-3">
                                    {{ $activities->links() }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @push('script-bottom')
        <script>
            function confirmSave() {
                return confirm("@lang('translation.groups.confirm_debt_update')");
            }

            // Client-side validation
            document.addEventListener('DOMContentLoaded', function() {
                const form = document.getElementById('edit-debt-form');
                const amountInput = form.querySelector('input[name="amount"]');
                const minAmount = {{ $item->payments->sum('amount') }};

                if (amountInput) {
                    amountInput.addEventListener('input', function() {
                        const value = parseFloat(this.value.replace(/[^0-9.-]+/g, '')) || 0;

                        if (value < minAmount && minAmount > 0) {
                            this.setCustomValidity("@lang('translation.groups.debt_cannot_be_less_than_paid_js', ['amount' => money($item->payments->sum('amount'))])");
                        } else {
                            this.setCustomValidity('');
                        }
                    });
                }

                // Show success toast if redirected with success message
                @if (session('success'))
                    // You can add toast notification here if you have a toast system
                    console.log('Success: {{ session('success') }}');
                @endif
            });
        </script>
    @endpush
@endsection
