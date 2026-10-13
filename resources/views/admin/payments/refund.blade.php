@extends('layouts.master')
@section('title')
    @lang('translation.payments.refund')
@endsection
@section('content')
    {{ Diglactic\Breadcrumbs\Breadcrumbs::render() }}
    <div class="row">
        <div class="col-md-4 col-sm-12">
            <div class="card card-height-100">
                <div class="card-header">
                    @lang('translation.payments.payment_info')
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-borderless">
                            <tr>
                                <th>ID</th>
                                <td>{{ $item->id }}</td>
                            </tr>
                            <tr>
                                <th>@lang('translation.students.student')</th>
                                <td>
                                    <a href="{{ route('admin.students.show', $item->student_id) }}" target="_blank"
                                        class="text-decoration-none">
                                        {{ $item->student->name ?? '-' }} <i class="mdi mdi-open-in-new"></i>
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <th>@lang('translation.students.payment_amount')</th>
                                <td class="fw-semibold">{{ money($item->amount) }}</td>
                            </tr>
                            @if ($item->payment_discounts)
                                <tr>
                                    <th>@lang('translation.payments.discount')</th>
                                    <td class="fw-semibold">
                                        {{ money($item->payment_discounts->sum('amount')) }}
                                    </td>
                                </tr>
                            @endif
                            <tr>
                                <th>@lang('translation.payments.type-name')</th>
                                <td>
                                    <span class="badge bg-success-subtle text-success">
                                        {{ $item->payment_type?->name ?? '-' }}
                                    </span>
                                </td>
                            </tr>
                            @if ($item->student_group_payment_id)
                                <tr>
                                    <th>@lang('translation.groups.group')</th>
                                    <td>
                                        <a href="{{ route('admin.groups.show', $item->student_group_payment->group->id ?? 0) }}"
                                            target="_blank" class="text-decoration-none">
                                            {{ $item->student_group_payment->group->name ?? '-' }} <i
                                                class="mdi mdi-open-in-new"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endif
                            <tr>
                                <th>@lang('translation.branches.branch')</th>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary">
                                        <i class="ri-git-branch-line"></i>
                                        {{ $item->branches()->first()?->name ?? '-' }}
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-8 col-sm-12">
            <div class="card card-height-100">
                <div class="card-header">
                    @lang('translation.payments.refund')
                    <i class="fas fa-info-circle text-primary cursor-pointer" data-bs-toggle="tooltip"
                        title="{{ __('translation.payments.refund_view') }}"></i>
                </div>
                <div class="card-body">
                    <!-- Payment Type -->
                    <div class="mb-4">
                        <h6 class="fw-bold mb-2">@lang('translation.payments.payment_type'):</h6>
                        <span class="badge bg-primary fs-6">
                            @if ($item->student_group_payment_id)
                                @lang('translation.payments.payment_for_debt')
                            @else
                                @lang('translation.payments.payment_for_balance')
                            @endif
                        </span>
                    </div>

                    <!-- Refund Flow Information -->
                    <div class="mb-4">
                        <h6 class="fw-bold mb-3">@lang('translation.payments.refund_flow'):</h6>

                        @if ($item->student_group_payment_id && $item->student_group_payment)
                            @php
                                $group = $item->student_group_payment->group()->withTrashed()->first();
                                $groupAccount = $group?->account ?? null;
                                $isGroupDeleted = $group?->deleted_at !== null;
                            @endphp

                            @if ($group && $groupAccount && !$isGroupDeleted)
                                <!-- Normal Group Refund -->
                                <div class="alert alert-info">
                                    <h6 class="alert-heading"><i class="mdi mdi-information me-2"></i>@lang('translation.payments.normal_group_refund')
                                    </h6>
                                    <p class="mb-0">@lang('translation.payments.normal_group_refund_description')</p>
                                </div>
                                <div class="row align-items-center text-center">
                                    <div class="col-3">
                                        <div class="p-3 bg-light rounded">
                                            <i class="mdi mdi-account-group fs-4 text-primary"></i>
                                            <div class="mt-2"><strong>{{ $group->name }}</strong></div>
                                            <small class="text-muted">@lang('translation.accounts.group_account')</small>
                                        </div>
                                    </div>
                                    <div class="col-2">
                                        <i class="mdi mdi-arrow-right fs-1 text-primary"></i>
                                    </div>
                                    <div class="col-2">
                                        <div class="p-2 bg-success text-white rounded">
                                            <strong>{{ money($item->amount + $item->payment_discounts->sum('amount')) }}</strong>
                                        </div>
                                    </div>
                                    <div class="col-2">
                                        <i class="mdi mdi-arrow-right fs-1 text-primary"></i>
                                    </div>
                                    <div class="col-3">
                                        <div class="p-3 bg-light rounded">
                                            <i class="mdi mdi-account fs-4 text-success"></i>
                                            <div class="mt-2">
                                                <strong>{{ $item->student?->getFullName() ?? '-' }}</strong>
                                            </div>
                                            <small class="text-muted">@lang('translation.accounts.student_account')</small>
                                        </div>
                                    </div>
                                </div>
                            @else
                                @if ($group)
                                    <!-- Group Deleted - Split Refund -->
                                    <div class="alert alert-warning">
                                        <h6 class="alert-heading"><i
                                                class="mdi mdi-alert-triangle me-2"></i>@lang('translation.payments.group_account_deleted')
                                        </h6>
                                        <p class="mb-0">@lang('translation.payments.group_account_deleted_description')</p>
                                    </div>

                                    @php
                                        $teacher = $group->teacher()->withTrashed()->first();
                                        $teacherPercentage = $teacher->percentage ?? 0;
                                        $mainPercentage = 100 - $teacherPercentage;
                                        $totalAmount = $item->amount + $item->payment_discounts->sum('amount');
                                        $teacherAmount = ($totalAmount * $teacherPercentage) / 100;
                                        $mainAmount = ($totalAmount * $mainPercentage) / 100;
                                    @endphp

                                    @if ($teacherAmount > 0)
                                        <div class="mb-3">
                                            <h6 class="text-muted">@lang('translation.payments.teacher_refund') ({{ $teacherPercentage }}%):</h6>
                                            <div class="row align-items-center text-center">
                                                <div class="col-3">
                                                    <div class="p-3 bg-light rounded">
                                                        <i class="mdi mdi-account-tie fs-4 text-warning"></i>
                                                        <div class="mt-2">
                                                            <strong>{{ $teacher->name ?? __('translation.users.teacher') }}</strong>
                                                        </div>
                                                        <small class="text-muted">@lang('translation.accounts.teacher_account')</small>
                                                    </div>
                                                </div>
                                                <div class="col-2">
                                                    <i class="mdi mdi-arrow-right fs-1 text-primary"></i>
                                                </div>
                                                <div class="col-2">
                                                    <div class="p-2 bg-warning text-dark rounded">
                                                        <strong>{{ money($teacherAmount) }}</strong>
                                                    </div>
                                                </div>
                                                <div class="col-2">
                                                    <i class="mdi mdi-arrow-right fs-1 text-primary"></i>
                                                </div>
                                                <div class="col-3">
                                                    <div class="p-3 bg-light rounded">
                                                        <i class="mdi mdi-account fs-4 text-success"></i>
                                                        <div class="mt-2">
                                                            <strong>{{ $item->student?->getFullName() ?? '-' }}</strong>
                                                        </div>
                                                        <small class="text-muted">@lang('translation.accounts.student_account')</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    @if ($mainAmount > 0)
                                        <div class="mb-3">
                                            <h6 class="text-muted">@lang('translation.payments.main_account_refund') ({{ $mainPercentage }}%):</h6>
                                            <div class="row align-items-center text-center">
                                                <div class="col-3">
                                                    <div class="p-3 bg-light rounded">
                                                        <i class="mdi mdi-bank fs-4 text-info"></i>
                                                        <div class="mt-2"><strong>@lang('attr.main_account')</strong></div>
                                                        <small class="text-muted">@lang('translation.accounts.main_account')</small>
                                                    </div>
                                                </div>
                                                <div class="col-2">
                                                    <i class="mdi mdi-arrow-right fs-1 text-primary"></i>
                                                </div>
                                                <div class="col-2">
                                                    <div class="p-2 bg-info text-white rounded">
                                                        <strong>{{ money($mainAmount) }}</strong>
                                                    </div>
                                                </div>
                                                <div class="col-2">
                                                    <i class="mdi mdi-arrow-right fs-1 text-primary"></i>
                                                </div>
                                                <div class="col-3">
                                                    <div class="p-3 bg-light rounded">
                                                        <i class="mdi mdi-account fs-4 text-success"></i>
                                                        <div class="mt-2">
                                                            <strong>{{ $item->student?->getFullName() ?? '-' }}</strong>
                                                        </div>
                                                        <small class="text-muted">@lang('translation.accounts.student_account')</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endif
                            @endif

                            <!-- Discount Refunds -->
                            @if ($item->payment_discounts->count())
                                <div class="mt-4">
                                    <h6 class="fw-bold mb-3">@lang('translation.payments.discount_refunds'):</h6>
                                    @foreach ($item->payment_discounts as $discount)
                                        <div class="row align-items-center text-center mb-2">
                                            <div class="col-3">
                                                <div class="p-2 bg-light rounded">
                                                    <strong>{{ $discount->transaction->from_account->name ?? '-' }}</strong>
                                                    <br><small class="text-muted">@lang('translation.accounts.discount_account')</small>
                                                </div>
                                            </div>
                                            <div class="col-2">
                                                <i class="mdi mdi-arrow-right fs-1 text-primary"></i>
                                            </div>
                                            <div class="col-2">
                                                <div class="p-2 bg-secondary text-white rounded">
                                                    <strong>{{ money($discount->amount) }}</strong>
                                                </div>
                                            </div>
                                            <div class="col-2">
                                                <i class="mdi mdi-arrow-right fs-1 text-primary"></i>
                                            </div>
                                            <div class="col-3">
                                                <div class="p-2 bg-light rounded">
                                                    <strong>{{ $item->student?->getFullName() ?? '-' }}</strong>
                                                    <br><small class="text-muted">@lang('translation.accounts.student_account')</small>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        @else
                            <!-- Balance Payment Refund -->
                            <div class="alert alert-info">
                                <h6 class="alert-heading"><i class="mdi mdi-information me-2"></i>@lang('translation.payments.balance_refund')
                                </h6>
                                <p class="mb-0">@lang('translation.payments.balance_refund_description')</p>
                            </div>
                            <div class="row align-items-center text-center">
                                <div class="col-4">
                                    <div class="p-3 bg-light rounded">
                                        <i class="mdi mdi-account fs-4 text-success"></i>
                                        <div class="mt-2"><strong>{{ $item->student?->getFullName() ?? '-' }}</strong>
                                        </div>
                                        <small class="text-muted">@lang('translation.accounts.student_account')</small>
                                    </div>
                                </div>
                                <div class="col-2">
                                    <i class="mdi mdi-arrow-left fs-1 text-primary"></i>
                                </div>
                                <div class="col-2">
                                    <div class="p-2 bg-success text-white rounded">
                                        <strong>{{ money($item->amount) }}</strong>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="p-3 bg-light rounded">
                                        <i class="mdi mdi-cash fs-4 text-primary"></i>
                                        <div class="mt-2"><strong>@lang('translation.payments.refund')</strong></div>
                                        <small class="text-muted">@lang('translation.payments.balance_increase')</small>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="mb-4">
                        <h6 class="fw-bold mb-3">@lang('translation.payments.comment'):</h6>
                        <textarea class="form-control" name="comment" id="comment" rows="3" placeholder="@lang('translation.payments.refund_comment')">{{ request()->get('comment') ?? '' }}</textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="d-flex justify-content-end">
        <form action="{{ route('admin.payments.refund-store', $item) }}" method="POST">
            @csrf
            <input type="hidden" name="comment" id="comment-hidden">
            <button class="btn btn-primary" onclick="copyCommentAndSubmit()">
                <i class="mdi mdi-check"></i>
                @lang('translation.payments.refund')
            </button>
        </form>
    </div>

    <script>
        function copyCommentAndSubmit() {
            // Copy comment from textarea to hidden input
            const commentTextarea = document.getElementById('comment');
            const commentHidden = document.getElementById('comment-hidden');
            commentHidden.value = commentTextarea.value;

            // Show confirmation dialog
            if (confirm('@lang('translation.payments.refund_confirm')')) {
                // Submit the form
                document.querySelector('form').submit();
            }
        }
    </script>
@endsection
