<!-- Debt Modal -->
<div wire:ignore.self class="modal fade" id="studentGroupPaymentModal" tabindex="-1"
    aria-labelledby="studentGroupPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="studentGroupPaymentModalLabel">
                    @lang("translation.groups.debt_info")
                </h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @if($selected_student_group_payment)
                @php
                $payment = $selected_student_group_payment->payments->sum('amount');
                $discount = $selected_student_group_payment->payments->sum(fn($item) =>
                $item->payment_discounts->sum('amount'));
                $balance = $selected_student_group_payment->amount - $selected_student_group_payment->left_amount -
                $discount - $payment;
                @endphp
                <table class="table">
                    <tr>
                        <th>ID</th>
                        <td>{{$selected_student_group_payment->id}}</td>
                    </tr>
                    <tr>
                        <th>@lang("translation.students.student")</th>
                        <td>
                            <a href="{{route('admin.students.show', $selected_student_group_payment->student_id)}}"
                                class="d-flex align-items-center gap-1">
                                <i class="ri-user-3-fill"></i>
                                {{$selected_student_group_payment->student?->getFullName() ?? '-'}}
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <th>@lang("translation.students.debt_amount")</th>
                        <td>{{money($selected_student_group_payment->amount)}}</td>
                    </tr>
                    <tr>
                        <th>@lang("translation.groups.left_amount")</th>
                        <td>
                            <span class="text-danger fw-semibold">
                                {{money($selected_student_group_payment->left_amount)}}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>@lang("translation.students.paid_amount")</th>
                        <td>
                            <span class="text-success fw-semibold">
                                {{money($payment)}}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>@lang("translation.students.discount")</th>
                        <td>{{money($discount)}}</td>
                    </tr>
                    <tr>
                        <th>@lang("translation.students.balance_amount")</th>
                        <td>{{money($balance)}}</td>
                    </tr>
                    <tr>
                        <th>@lang("translation.groups.debt_range")</th>
                        <td>
                            {{ ($group->payment_type == 'date_to_date') ?
                            debtRange($selected_student_group_payment->range) : $selected_student_group_payment->range
                            }}
                        </td>
                    </tr>
                </table>
                <br>
                <h5>@lang("translation.students.payments")</h5>
                <table class="table">
                    <thead class="bg-primary-subtle">
                        <tr>
                            <th>ID</th>
                            <th>@lang("translation.students.payment_amount")</th>
                            <th>@lang("translation.students.discount_amount")</th>
                            <th>@lang("translation.students.payment_type")</th>
                            <th>@lang("translation.students.paid_at")</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($selected_student_group_payment->payments as $payment)
                        <tr>
                            <td>{{$payment->id}}</td>
                            <td>{{money($payment->amount)}}</td>
                            <td>{{money($payment->payment_discounts?->sum('amount') ?? 0)}}</td>
                            <td>{{$payment->payment_type?->name ?? '-'}}</td>
                            <td>{{dateFormat($payment->date)}}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="100">
                                <x-no-result />
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                @else
                <x-no-result />
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">@lang("translation.Close")</button>
            </div>
        </div>
    </div>
</div>
