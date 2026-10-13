<div>
    <div class="card rounded-4">
        <div class="card-body">
            <h5 class="mb-4 d-flex justify-content-between">
                <div>
                    {{ __('translation.users.students_payment_percentage') }}
                </div>
                <div>
                    <select class="form-select form-select-sm" wire:model.live="selectedDate">
                        <option value="this_month">
                            {{ __('translation.users.this_month') }}
                        </option>
                        <option value="last_month">
                            {{ __('translation.users.last_month') }}
                        </option>
                        <option value="all_time">
                            {{ __('translation.users.all_time') }}
                        </option>
                    </select>
                </div>
            </h5>
            @php
                $teacherProfit = 0;
                $allDebtSum = 0;
                $leftDebtSum = 0;
                foreach ($studentsPaymentsData['groups'] as $group) {
                    $allDebtSum += $group['allDebtSum'];
                    $leftDebtSum += $group['leftDebtSum'];
                    $teacherProfit += $group['teacherProfit'];
                }
                $percentage = 0;

                if ($allDebtSum > 0) {
                    $percentage = round((($allDebtSum - $leftDebtSum) / $allDebtSum) * 100);
                }
            @endphp
            <div class="progress animated-progress custom-progress progress-label">
                <div class="progress-bar bg-danger" role="progressbar" style="width: {{ $percentage }}%"
                    aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100">
                    <div class="label">{{ $percentage }}%</div>
                </div>
            </div>
            <div class="mt-3 table-responsive">
                <table class="table mb-0 table-borderless">
                    <tbody>
                        <tr>
                            <th class="ps-0" scope="row">{{ __('translation.users.all_students_debt_amount') }} :
                            </th>
                            <td class="text-muted">{{ money($allDebtSum) }}</td>
                        </tr>
                        <tr>
                            <th class="ps-0" scope="row">{{ __('translation.users.left_amount') }}
                                :
                            </th>
                            <td class="text-danger fw-semibold">{{ money($leftDebtSum) }}</td>
                        </tr>
                        <tr>
                            <th class="ps-0" scope="row">{{ __('translation.users.paid_amount') }}
                                :
                            </th>
                            <td class="text-muted">{{ money($allDebtSum - $leftDebtSum) }}</td>
                        </tr>
                        <tr>
                            <th class="ps-0" scope="row">{{ __('translation.users.teacher_profit') }}
                                :
                            </th>
                            <td class="text-muted">{{ money($teacherProfit) }}</td>
                        </tr>
                    </tbody>
                </table>
                <div class="mt-3 text-end">
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                        data-bs-target="#teacherDetailsModal">
                        {{ __('translation.show_more') }}
                    </button>
                </div>
            </div>
        </div>
        <div class="card-preloader d-none" wire:loading.class.remove="d-none">
            <div class="card-status">
                <div class="spinner-border text-success">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Teacher Details Modal -->
    <div class="modal fade" id="teacherDetailsModal" tabindex="-1" aria-labelledby="teacherDetailsModalLabel"
        aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="teacherDetailsModalLabel">{{ __('translation.users.teacher_details') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex justify-content-end mb-3">
                        <select class="form-select form-select-sm w-auto" wire:model.live="selectedDate">
                            <option value="this_month">{{ __('translation.users.this_month') }}</option>
                            <option value="last_month">{{ __('translation.users.last_month') }}</option>
                            <option value="all_time">{{ __('translation.users.all_time') }}</option>
                        </select>
                    </div>
                    <div class="table-responsive" wire:loading.class="opacity-50">
                        <table class="table table-bordered table-hover align-middle">
                            <thead class="table-light">
                                <tr class="text-center">
                                    <th>{{ __('translation.groups.group') }}</th>
                                    <th>{{ __('translation.users.teacher_percentage') }}</th>
                                    <th>{{ __('translation.users.all_debt') }}</th>
                                    <th>{{ __('translation.users.left_amount') }}</th>
                                    <th>{{ __('translation.users.paid_amount') }}</th>
                                    <th>{{ __('translation.users.teacher_profit') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($studentsPaymentsData['groups'] as $group)
                                    <tr>
                                        <td class="fw-medium">{{ $group['name'] }}</td>
                                        <td class="text-center">
                                            {{ $group['teacher_percentage'] ?? $user->percentage }}%</td>
                                        <td class="text-end text-nowrap">{{ money($group['allDebtSum']) }}</td>
                                        <td class="text-end text-nowrap text-danger">{{ money($group['leftDebtSum']) }}
                                        </td>
                                        <td class="text-end text-nowrap text-success">
                                            {{ money($group['allDebtSum'] - $group['leftDebtSum']) }}</td>
                                        <td class="text-end text-nowrap fw-medium">{{ money($group['teacherProfit']) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="2" class="fw-bold text-end">{{ __('translation.total') }}</td>
                                    <td class="text-end text-nowrap"><strong>{{ money($allDebtSum) }}</strong></td>
                                    <td class="text-end text-nowrap text-danger">
                                        <strong>{{ money($leftDebtSum) }}</strong></td>
                                    <td class="text-end text-nowrap text-success">
                                        <strong>{{ money($allDebtSum - $leftDebtSum) }}</strong></td>
                                    <td class="text-end text-nowrap fw-bold">{{ money($teacherProfit) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">{{ __('translation.close') }}</button>
                </div>
            </div>
        </div>
    </div>
</div>
