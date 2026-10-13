<div class="card-body">
    @if($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <div class="mb-3">
        <label for="studentGroupPayment">
            @lang("translation.students.full_name")
        </label>
        <div class="bg-secondary-subtle p-2 rounded">
            <a href="{{ route('admin.students.show', $item->id) }}" class="text-reset">
                {{$item->getFullName()}} | {{$item->getFormattedPhone()}}
            </a>
        </div>
    </div>
    <div class="form-group mb-3">
        <label for="payment-type">
            @lang("translation.students.payment_type")
        </label>
        <select class="form-control @error('payment_type_id') is-invalid @enderror" id="payment-type"
                name="payment_type_id">
            @foreach($paymentTypes as $paymentType)
                <option value="{{$paymentType->id}}"
                        @if(isset($payment) && $payment->payment_type_id == $paymentType->id) selected @endif>
                    {{$paymentType->name}}
                </option>
            @endforeach
        </select>
        @error('payment_type_id')
        <div class="invalid-feedback">
            {{$message}}
        </div>
        @enderror
    </div>
    @if($unpaidMonths->count() > 0)
        <div class="form-group mb-3">
            <label for="unpaid-month">
                @lang("translation.students.choose_month")
            </label>
            <select class="form-control @error('month') is-invalid @enderror" id="unpaid-month" name="month">
                @foreach($unpaidMonths as $month)
                    <option value="{{$month->id}}"
                            @if($item->student_group_payment_id == $month->id) selected @endif>
                        @if($month->group->deleted_at == null)
                            {{$month->group->teacher->name}} - {{$month->group->name}}
                            ({{dateFormat($month->created_at, 'F Y')}}) | {{money($month->left_amount)}}
                        @else
                            <del>
                                (@lang("translation.groups.deleted"))
                                {{$month->group->teacher->name}} - {{$month->group->name}}
                                ({{dateFormat($month->created_at, 'F Y')}}) | {{money($month->left_amount)}}
                            </del>
                        @endif
                    </option>
                @endforeach
            </select>
            @error('month')
            <div class="invalid-feedback">
                {{$message}}
            </div>
            @enderror
        </div>
    @else
        @if(isset($payment))
            <div class="mb-3">
                <label for="studentGroupPayment">
                    @lang("translation.students.payment_for")
                </label>
                <div class="bg-secondary-subtle p-2 rounded">
                    <a href="{{ route('admin.groups.show', $payment->group->id) }}" class="text-reset">
                        {{$payment->group->teacher->name}} - {{$payment->group->name}}
                        ({{dateFormat($payment->studentGroupPayment->created_at, 'F Y')}})
                    </a>
                </div>
            </div>
        @endif
    @endif
    <x-money-input
        :label="__('translation.students.payment_amount')"
        name="amount"
        :attribute="(isset($payment) && $payment->status == 'draft') ? 'required' : 'disabled'"
        :placeholder="__('translation.students.payment_amount')"
        :value="$payment->amount ?? abs($item->balance) ?? old('amount') ?? ''"
    />
    @if(isset($payment) && $payment->status == 'saved')
        <input type="hidden" name="method_type" value="updateSavedPayment">
        <input type="hidden" name="amount" value="{{ $payment->amount }}">
    @endif
    @if($unpaidMonths->count() > 0 && $item->status == 'draft')
        <!-- discount switcher -->
        <div class="form-check form-switch mb-3">
            <input class="form-check-input" type="checkbox" role="switch" id="discountSwitch">
            <label class="form-check-label" for="discountSwitch">
                @lang("translation.students.discount")
            </label>
        </div>
        <!-- date switcher -->
        <div class="form-check form-switch mb-3">
            <input class="form-check-input" type="checkbox" role="switch" id="dateSwitch">
            <label class="form-check-label" for="dateSwitch">
                @lang("translation.students.payment_date_switch")
            </label>
        </div>
    @endif
    <div id="discount-div">
        <div class="form-group mb-3">
            <label for="discount_account">
                @lang("translation.students.discount_account")
            </label>
            <select name="discount_account_id" id="discount_account"
                    class="form-control @error('discount_account_id') is-invalid @enderror">
                <option value="both">
                    @lang("translation.students.discount_both")
                </option>
                @foreach($discountAccounts as $account)
                    <option value="{{$account->id}}">
                        @if($account->account_code == 1000)
                            {{$account->account_code}} - @lang("translation.students.main_account")
                        @else
                            {{$account->account_code}} - {{$account->name}}
                        @endif
                    </option>
                @endforeach
            </select>
            @error('discount_account_id')
            <div class="invalid-feedback">
                {{$message}}
            </div>
            @enderror
        </div>
        <x-money-input
            :label="__('translation.students.discount_amount')"
            name="discount_amount"
            :placeholder="__('translation.students.discount_amount')"
            :value="old('discount_amount') ?? ''"
        />
    </div>
    <div class="form-group mb-3" id="date-container">
        <label for="paid_at">@lang("translation.students.payment_date")</label>
        <input type="date" id="paid_at" name="date"
               class="form-control @error('date') is-invalid @enderror">
        @error('date')
        <div class="invalid-feedback">
            {{ $message }}
        </div>
        @enderror
    </div>
    @push('script-bottom')
        <script>
            $(document).ready(function () {
                $('#discount-div').hide();
                $('#discountSwitch').change(function () {
                    if ($(this).is(':checked')) {
                        $('#discount-div').show();
                    } else {
                        $('#discount-div').hide();
                    }
                });
                $('#date-container').hide();
                $('#dateSwitch').change(function () {
                    if ($(this).is(':checked')) {
                        $('#date-container').show();
                    } else {
                        $('#date-container').hide();
                    }
                });
            });
        </script>
    @endpush
</div>
