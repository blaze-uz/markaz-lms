<div class="card-body">
    <x-input :label="__('translation.users.name')"
             name="name"
             :placeholder="__('translation.users.name')"
             attribute="disabled"
             value="{{$item->name}}"
    />
    <div class="col-md-6 col-sm-12 mb-3 w-100">
        <label for="">{{__("attr.payment_method")}}</label>
        <div class="d-flex gap-2 flex-wrap">
            <div class="mr-3">
                <input
                    type="radio"
                    class="btn-check"
                    name="payment_type"
                    id="primary-outlined"
                    value="salary"
                    checked>
                <label class="btn btn-outline-primary" for="primary-outlined">
                    <div class="d-flex gap-2 align-items-center">
                        <i class="fa-solid fa-wallet"></i>
                        {{__("translation.payments.salary")}} /
                        {{__("translation.payments.bonus")}}
                    </div>
                </label>
            </div>
            <div>
                <input
                    type="radio"
                    class="btn-check"
                    name="payment_type"
                    id="bonus-payment-type"
                    value="group">
                <label class="btn btn-outline-primary" for="bonus-payment-type">
                    <div class="d-flex gap-2 align-items-center">
                        <i class="fa-solid fa-coins"></i>
                        {{__("translation.payments.get_from_groups")}}
                    </div>
                </label>
            </div>
            <div>
                <input
                    type="radio"
                    class="btn-check"
                    name="payment_type"
                    id="withdraw-payment-type"
                    value="withdraw">
                <label class="btn btn-outline-primary text-nowrap" for="withdraw-payment-type">
                    <div class="d-flex gap-2 align-items-center">
                        <i class="fa-solid fa-money-bill-transfer"></i>
                        {{__("translation.payments.withdraw")}} /
                        {{__("translation.payments.advance")}}
                    </div>
                </label>
            </div>
            @error('payment_type')
            <div class="text-danger">* {{$message}}</div>
            @enderror
        </div>
    </div>
    <div id="groupsInfo" class="p-2" style="display: none">
        <table class="table table-sm" style="padding: 0!important; margin: 0 !important;">
            <tr>
                <th>ID</th>
                <th>@lang("translation.groups.name")</th>
                <th>@lang("translation.groups.balance")</th>
                <th></th>
            </tr>
            @php
                $total_amount = 0;
                $teacher_amount = 0;
                $main_account_amount = 0;
            @endphp
            @foreach($groups as $group)
                @php
                    $group_balance = $group->account?->balance ?? 0;
                    $total_amount += $group_balance;

                    $teacher_percentage = $group->teacher_percentage ?? $item->percentage;
                    $current_group_main_account_amount = intval($group_balance * (100 - $teacher_percentage) / 100);
                    $current_group_teacher_amount = $group_balance - $current_group_main_account_amount;

                    $main_account_amount += $current_group_main_account_amount;
                    $teacher_amount += $current_group_teacher_amount;
                @endphp
                <tr>
                    <td>{{ $group->id }}</td>
                    <td>{{ $group->name }}</td>
                    <td>{{ money($group_balance) }} - {{ $teacher_percentage }} %</td>
                    <td></td>
                </tr>
            @endforeach
            <tr>
                <td colspan="2">
                    @lang("attr.total"):
                </td>
                <td>
                    {{money($total_amount)}}
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    @lang("attr.for_main_account"):
                </td>
                <td>
                    {{money($main_account_amount)}}
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <b>@lang("attr.for_teacher_account")</b>:
                </td>
                <td>
                    {{money($teacher_amount)}}
                </td>
            </tr>
        </table>
    </div>
    <div class="form-group mb-3" id="paymentMonthWrap" style="display: none;">
        <label for="payment_month">
            @lang("translation.payments.for_month")
        </label>
        <input type="month"
               id="payment_month"
               name="payment_month"
               class="form-control @error('payment_month') is-invalid @enderror"
               value="{{ old('payment_month', now()->format('Y-m')) }}"
               max="{{ now()->format('Y-m') }}">
        @error('payment_month')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="form-group mb-3" id="accountSelect">
        <label for="unpaid-month">
            @lang("translation.users.select_account")
        </label>
        <select id="account" class="form-select @error('account_id') is-invalid @enderror" autocomplete="off"
                name="account_id">
            <option></option>
            @foreach($accounts as $account)
                <option value="{{$account->id}}" {{old('account_id') == $account->id ? 'selected' : ''}}>
                    {{$account->name}} | @lang('attr.balance'): {{money($account->getBalance())}}
                </option>
            @endforeach
        </select>
    </div>
    <div id="amount">
        <x-money-input
                :label="__('translation.users.payment_amount')"
                name="amount"
                :placeholder="__('translation.users.payment_amount')"
                :value="old('amount') ?? ''"
        />
    </div>

    @push('script-bottom')
        <script>
            $(document).ready(function () {
                function togglePaymentMonth(show) {
                    if (show) $('#paymentMonthWrap').show(); else $('#paymentMonthWrap').hide();
                }
                $('[name=payment_type]').change(function () {
                    let type = $(this).val();
                    switch (type) {
                        case 'salary':
                            $('#accountSelect').show();
                            $('#groupsInfo').hide();
                            $('#amount').show();
                            togglePaymentMonth(true);
                            break;
                        case 'group':
                            $('#accountSelect').hide();
                            $('#groupsInfo').show();
                            $('#amount').hide();
                            togglePaymentMonth(false);
                            break;
                        case 'withdraw':
                            $('#accountSelect').hide();
                            $('#groupsInfo').hide();
                            $('#amount').show();
                            togglePaymentMonth(true);
                            break;
                    }
                });
                $('[name=payment_type]:checked').trigger('change');
            });
        </script>
    @endpush
</div>
