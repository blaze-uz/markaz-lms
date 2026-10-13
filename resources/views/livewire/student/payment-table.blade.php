<div>
    <div>
        <div class="row">
            <div class="col-md-5 col-sm-6"></div>
            <div class="col-md-7 col-sm-6">
                <div class="gap-2 d-flex justify-content-between w-100" wire:click="filter">
                    <select name="month" id="month" class="form-control" wire:model="selectedMonth">
                        <option value="">@lang('translation.students.select_month')</option>
                        @foreach ($months as $month)
                            <option value="{{ $month->id }}">{{ $month->group?->name ?? '-' }} -
                                {{ dateFormat($month->date, 'F
                                Y') }}</option>
                        @endforeach
                    </select>
                    <button class="btn btn-primary">
                        @lang('translation.Filter')
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table">
            <tr>
                <th>ID</th>
                <th>@lang('translation.students.payment_date')</th>
                <th>@lang('translation.students.which_month')</th>
                <th>@lang('translation.students.payment_type')</th>
                <th>@lang('translation.students.payment_amount')</th>
                <th>@lang('translation.students.discount_amount')</th>
                <td></td>
            </tr>
            @forelse($payments as $payment)
                <tr>
                    <td>{{ $payment->id ?? '-' }}</td>
                    <td class="text-nowrap">
                        {{ dateFormat($payment->date) }} @if ($payment->refunds()->count())
                            <span class="badge bg-danger-subtle text-danger">
                                @lang('translation.payment_status.refund')
                            </span>
                        @endif
                        <br>
                        <small class="text-muted">
                            @lang('translation.expenses.created_by'):
                            <span class="badge bg-success-subtle text-success">
                                {{ $payment->creator?->name ?? '-' }}
                            </span>
                        </small>
                    </td>
                    @if ($payment->student_group_payment?->group)
                        <td class="text-nowrap">
                            @if (!$payment->student_group_payment?->group)
                                <span class="badge bg-success-subtle text-success">
                                    @lang('translation.students.payment_for_balance')
                                </span>
                            @endif
                            <a href="{{ route('admin.groups.show', $payment->student_group_payment->group->id ?? 0) }}"
                                class="text-reset">
                                {{ $payment->student_group_payment?->group->name ?? '-' }}
                            </a>
                            <br>
                            <small class="text-muted">
                                {{ $payment->student_group_payment?->range ?? '-' }}
                            </small>
                        </td>
                    @else
                        <td>
                            <span class="badge bg-success-subtle text-success">
                                @lang('translation.students.payment_for_balance')
                            </span>
                        </td>
                    @endif
                    <td class="text-nowrap">
                        {{ $payment->payment_type->name ?? '-' }}
                    </td>
                    <td class="text-nowrap">
                        {{ money($payment->amount) }}
                    </td>
                    <td class="text-nowrap">
                        {{ money($payment->payment_discounts()->sum('amount')) }}
                    </td>
                    <td style="width: 50px">
                        <div class="d-flex align-items-center">
                            <button class="btn text-muted" title="{{ __('translation.btn.show') }}"
                                data-bs-toggle="modal" data-bs-target="#paymentModal"
                                wire:click="openPayment({{ $payment->id }})">
                                <i class="mdi mdi-eye fs-5"></i>
                            </button>
                            @can('payment-edit')
                                <a href="{{ route('admin.payments.edit', $payment->id) }}" class="btn"
                                    title="{{ __('translation.payments.edit') }}" data-bs-toggle="tooltip">
                                    <i class="fa-solid fa-pen-to-square text-muted"></i>
                                </a>
                            @endcan
                            @if ($payment->refunds()->count() == 0)
                                @can('payment-refund')
                                    <a href="{{ route('admin.payments.refund', $payment->id) }}" class="btn text-muted"
                                        data-bs-toggle="tooltip" title="{{ __('translation.payments.refund') }}">
                                        <i class="fa-solid fa-arrow-rotate-left"></i>
                                    </a>
                                @endcan
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="100">
                        <x-no-result />
                    </td>
                </tr>
            @endforelse
        </table>
    </div>
    <div class="card-preloader d-none" wire:loading.class.remove="d-none">
        <div class="card-status">
            <div class="spinner-border text-success">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    </div>

    <!-- Payment modal -->
    <div wire:ignore.self class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="paymentModalLabel">@lang('translation.transaction')</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <table class="table">
                        <tr>
                            <th>ID</th>
                            <td>{{ $paymentModalItem?->id ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>@lang('translation.students.student')</th>
                            <td>{{ $paymentModalItem?->student->getFullName() ?? '-' }}</td>
                        </tr>
                        @if ($paymentModalItem?->student_group_payment?->group)
                            <tr>
                                <th>@lang('translation.groups.group')</th>
                                <td>
                                    <a
                                        href="{{ route('admin.groups.show', $paymentModalItem?->student_group_payment->group->id ?? 0) }}">
                                        {{ $paymentModalItem?->student_group_payment->group?->name ?? '-' }}
                                    </a><br>
                                    <span class="text-muted">
                                        @lang('translation.groups.course'):
                                    </span>
                                    {{ $paymentModalItem?->student_group_payment?->group->course->name ?? '-' }}
                                </td>
                            </tr>
                            <tr>
                                <th>@lang('translation.groups.debt_range')</th>
                                <td>{{ $paymentModalItem?->student_group_payment?->range ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>@lang('translation.groups.teacher')</th>
                                <td>
                                    <a
                                        href="{{ route('admin.users.show', $paymentModalItem?->student_group_payment->group->teacher_id ?? 0) }}">
                                        {{ $paymentModalItem?->student_group_payment->group?->teacher->name ?? '-' }}
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <th>@lang('translation.payments.payment_type')</th>
                                <td>{{ __('translation.payments.for_debt') }}</td>
                            </tr>
                        @else
                            <tr>
                                <th>@lang('translation.payments.payment_type')</th>
                                <td>{{ __('translation.payments.for_balance') }}</td>
                            </tr>
                        @endif
                        <tr>
                            <th>@lang('translation.payments.form_of_payment')</th>
                            <td>{{ $paymentModalItem?->payment_type->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>@lang('translation.payments.created_by')</th>
                            <td>
                                <a href="{{ route('admin.users.show', $paymentModalItem?->creator->id ?? 0) }}">
                                    {{ $paymentModalItem?->creator->name ?? '-' }}
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <th>@lang('translation.students.payment_amount')</th>
                            <td>{{ money($paymentModalItem?->amount) }}</td>
                        </tr>
                        <tr>
                            <th>@lang('translation.payments.comment')</th>
                            <td>{{ $paymentModalItem->comment ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>@lang('attr.create_date')</th>
                            <td>{{ dateFormat($paymentModalItem?->created_at) }}</td>
                        </tr>
                        @if ($paymentModalItem?->refunds()->count())
                            <tr>
                                <th>@lang('translation.payments.refund_comment')</th>
                                <td>
                                    @foreach ($paymentModalItem->refunds as $refund)
                                        {{ $refund?->comment ?? '-' }}<br>
                                    @endforeach
                                </td>
                            </tr>
                        @endif
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        @lang('translation.Close')
                    </button>
                    @canany(['payment-index', 'student-payment', 'student-show', 'user-payment-list'])
                        <a href="{{ route('admin.payments.print', $paymentModalItem->id ?? 0) }}" class="btn btn-primary">
                            <i class="fas fa-print"></i> Print
                        </a>
                    @endcanany
                </div>
            </div>
        </div>
    </div>
</div>
