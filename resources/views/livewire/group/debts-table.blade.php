<div>
    @push("css")
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    @endpush
    <div class="gap-2 mb-3 d-flex justify-content-end">
        <div style="width: 200px;" wire:ignore>
            <select id="debt_range" class="form-select" wire:model="debt_range">
                <option value="" disabled selected>{{ __('translation.choose_debt_range') }}</option>
                @foreach($debt_ranges as $debt_range)
                <option value="{{$debt_range}}">
                    {{ ($group->payment_type == 'date_to_date') ? $debt_range : $debt_range }}
                </option>
                @endforeach
            </select>
        </div>
        <div style="width: 150px">
            <select class="form-select" wire:model.live="filter">
                <option value="all">{{ __('translation.all') }}</option>
                <option value="unpaid">{{ __('translation.payment_status.unpaid') }}</option>
                <option value="paid">{{ __('translation.students.paid') }}</option>
            </select>
        </div>
    </div>
    <div class="mb-3 row g-3">
        <div class="col-sm-4">
            <div class="p-3 rounded border cursor-pointer bg-success-subtle h-100" data-bs-toggle="modal"
                data-bs-target="#paymentStatisticsModal">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="avatar-sm">
                            <div class="text-white rounded avatar-title bg-success">
                                <i class="ri-money-dollar-circle-line fs-2"></i>
                            </div>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <p class="mb-1 text-muted">@lang("translation.students.payment_amount")</p>
                        <h4 class="mb-0 text-success">{{ money($payment_amount) }}</h4>
                        <small class="gap-1 text-muted d-flex align-items-center">
                            <i class="ri-window-line"></i>
                            @lang("translation.click_to_show_payments")
                        </small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="p-3 rounded border bg-primary-subtle h-100">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="avatar-sm">
                            <div class="text-white rounded avatar-title bg-primary">
                                <i class="ri-percent-line fs-2"></i>
                            </div>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <p class="mb-1 text-muted">@lang("translation.students.discount_amount")</p>
                        <h4 class="mb-0 text-primary">{{ money($discount_amount) }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="p-3 rounded border bg-danger-subtle h-100">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="avatar-sm">
                            <div class="text-white rounded avatar-title bg-danger">
                                <i class="ri-refund-2-line fs-2"></i>
                            </div>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <p class="mb-1 text-muted">@lang("attr.left_debt_amount")</p>
                        <h4 class="mb-0 text-danger">{{ money($left_amount) }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table mb-0 align-middle table-nowrap">
            <thead>
                <tr>
                    <th class="py-2">ID</th>
                    <th class="py-2">@lang("translation.students.full_name")</th>
                    <th class="py-2">@lang("translation.students.payment_amount")</th>
                    <th class="py-2">@lang("attr.left_amount")</th>
                    <th class="py-2">@lang("translation.students.debt_range")</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($studentGroupPayments as $studentGroupPayment)
                <tr>
                    <td class="py-2">{{ $studentGroupPayment->id }}</td>
                    <td class="py-2 text-nowrap text-truncate" style="max-width: 270px">
                        <a href="{{ route('admin.students.show', $studentGroupPayment->student_id) }}"
                            class="text-reset text-decoration-none">
                            <span class="me-2">
                                {!!
                                Avatar::create($studentGroupPayment->student?->getFullName())->setTheme('grayscale-dark')->setDimension(32)->setFontSize(12)->toSvg()
                                !!}
                            </span>
                            {{ $studentGroupPayment->student?->getFullName() }}
                        </a>
                    </td>
                    <td class="py-2">
                        <span class="badge bg-success-subtle text-success">{{ money($studentGroupPayment->amount)
                            }}</span>
                    </td>
                    <td class="py-2">
                        <span class="badge bg-danger-subtle text-danger">{{ money($studentGroupPayment->left_amount)
                            }}</span>
                    </td>
                    <td class="py-2">
                        @if($group->payment_type == 'one_time')
                            <span class="badge bg-success-subtle text-success">
                                <i class="ri-coins-line me-1"></i>@lang('translation.groups.one_time')
                            </span>
                        @else
                            {{ $studentGroupPayment->range }}
                        @endif
                    </td>
                    <td class="py-2 text-end">
                        @if($studentGroupPayment->left_amount > 0)
                            @can('student-payment')
                                <a href="{{ route('admin.students.payment.view', ['student' => $studentGroupPayment->student_id]) }}"
                                    class="btn btn-sm btn-success">
                                    <i class="ri-money-dollar-circle-line me-1"></i>
                                    @lang("translation.btn.pay")
                                </a>
                            @endcan
                        @endif
                        @can('group-update-debt')
                        <a href="{{ route('admin.groups.edit-debt', $studentGroupPayment->id) }}"
                            class="btn btn-sm btn-primary">
                            <i class="ri-pencil-line me-1"></i>
                            @lang("translation.groups.edit-debt")
                        </a>
                        @endcan
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="100" class="text-center">
                        <x-no-result />
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="my-3">
            {{$studentGroupPayments->links()}}
        </div>
    </div>

    @include('admin.groups.partials.student-group-payment-modal')

    @include('admin.groups.partials.payment-statistics-modal')

    <div class="card-preloader d-none" wire:loading.class.remove="d-none">
        <div class="card-status">
            <div class="spinner-border text-success">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    </div>
    @push('custom-scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#debt_range').select2();
            $('#debt_range').on('change', function (e) {
                @this.set('debt_range', e.target.value);
            });
        });
    </script>
    @endpush
</div>