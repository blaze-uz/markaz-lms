<div class="row">
    <div class="col-md-8 col-sm-12">
        <div class="card">
            <div class="card-body">
                <div class="mb-3">
                    <label for="item">
                        @lang('translation.expenses.item_name')
                    </label>
                    <select id="item" class="tomSelect @error('item_id') is-invalid @enderror" autocomplete="off"
                        name="item_id">
                        <option></option>
                        @foreach($items as $item)
                        <option value="{{$item->id}}" {{old('item_id')==$item->id ? 'selected' : ''}}
                            @if(isset($expense) && $expense->item_id == $item->id) selected @endif>
                            {{$item->name}}
                        </option>
                        @endforeach
                    </select>
                    @error('item_id')
                    <div class="text-danger">
                        {{$message}}
                    </div>
                    @enderror
                </div>
                <div class="row">
                    <div class="mb-3 col-md-3">
                        <label for="qty">
                            @lang('translation.expenses.quantity')
                        </label>
                        <div class="gap-3 mb-3 d-flex qty-container">
                            <button type="button" class="mr-1 qty-btn-minus btn btn-sm btn-primary">
                                <i class="ri-subtract-line"></i>
                            </button>
                            <input type="number" id="qty" name="quantity" value="{{ $expense->quantity ?? 1 }}"
                                class="text-center input-qty input-cornered form-control" />
                            <button type="button" class="ml-1 qty-btn-plus btn btn-sm btn-primary btn-cornered">
                                <i class="ri-add-line"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-9 col-sm-12">
                        <x-money-input name="amount" :label="__('translation.expenses.price')"
                            :value="$expense->price ?? ''" />
                    </div>
                </div>
                <div class="mb-3">
                    <label for="place">
                        @lang('translation.expenses.place_name')
                        <small class="text-muted">
                            (e.g. @lang('translation.expenses.place_name_example'))
                        </small>
                    </label>
                    <select id="place" class="tomSelect" autocomplete="off" name="place_id">
                        <option></option>
                        @foreach($places as $place)
                        <option value="{{$place->id}}" {{old('place_id')==$place->id ? 'selected' : ''}}
                            @if(isset($expense) && $expense->place_id == $place->id) selected @endif>
                            {{$place->name}}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label for="account">
                        @lang('translation.expenses.which_account')
                    </label>
                    <select id="account" class="accountSelect @error('account_id') is-invalid @enderror"
                        autocomplete="off" name="account_id">
                        <option></option>
                        @foreach($accounts as $account)
                        <option value="{{$account->id}}" {{old('account_id')==$account->id ? 'selected' : ''}}
                            @if(isset($expense) && $expense->account_id == $account->id) selected @endif>
                            {{$account->name}}
                        </option>
                        @endforeach
                    </select>
                    @error('account_id')
                    <div class="text-danger">
                        {{$message}}
                    </div>
                    @enderror
                </div>
                <!-- date switcher -->
                <div class="mb-3 form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="dateSwitch">
                    <label class="form-check-label" for="dateSwitch">
                        @lang("translation.students.payment_date_switch")
                    </label>
                </div>
                <div class="mb-3 form-group d-none" id="date-container">
                    <label for="paid_at">@lang("translation.students.payment_date")</label>
                    <input type="date" id="paid_at" name="date"
                        class="form-control @error('date') is-invalid @enderror">
                    @error('date')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-sm-12">
        <div class="card">
            <div class="card-body">
                <label for="">{{__("translation.payments.choose_payment_type")}}</label>
                <div class="flex-wrap gap-2 d-flex justify-content-start">
                    @foreach($paymentTypes as $key => $paymentType)
                    <div class="mr-3">
                        <input type="radio" class="btn-check" name="payment_type_id"
                            id="payment-type-{{$paymentType->id}}" value="{{$paymentType->id}}" @if(isset($expense) &&
                            $expense->payment_type_id == $paymentType->id) checked @endif
                        @if(!isset($expense) && $key == 0) checked @endif>
                        <label class="btn btn-outline-primary" for="payment-type-{{$paymentType->id}}">
                            <i class="mdi mdi-cash-multiple"></i>
                            {{$paymentType->name}}
                        </label>
                    </div>
                    @endforeach
                    @error('payment_type_id')
                    <div class="text-danger">* {{$message}}</div>
                    @enderror
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="mb-3">
                    <label for="description">
                        @lang('translation.expenses.description')
                    </label>
                    <textarea id="description" name="description"
                        class="form-control @error('description') is-invalid @enderror"
                        rows="3">{{ $expense->description ?? '' }}</textarea>
                    @error('description')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>
            </div>
        </div>
    </div>
</div>
@push('custom-scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
<script>
    document.querySelectorAll('.tomSelect').forEach((el) => {
            let settings = {
                create: true,
                createOnBlur: true,
                persist: false,
            };
            new TomSelect(el, settings);
        });

        new TomSelect(document.querySelector('.accountSelect'), {})

        var buttonPlus = $(".qty-btn-plus");
        var buttonMinus = $(".qty-btn-minus");

        let incrementPlus = buttonPlus.click(function (e) {
            e.preventDefault();
            var $n = $(this)
                .parent(".qty-container")
                .find(".input-qty");
            let amount = $n.val(Number($n.val()) + 1);
            if (amount === 1) {
                buttonMinus.prop('disabled', true)
            } else {
                buttonMinus.prop('disabled', false)
            }
        });

        let incrementMinus = buttonMinus.click(function (e) {
            e.preventDefault();
            var $n = $(this)
                .parent(".qty-container")
                .find(".input-qty");
            var amount = Number($n.val());
            if (amount > 1) {
                $n.val(amount - 1);
            }
            if (amount === 1) {
                buttonMinus.prop('disabled', true)
            } else {
                buttonMinus.prop('disabled', false)
            }
        });

        $('#dateSwitch').change(function () {
            if ($(this).is(':checked')) {
                $('#date-container').removeClass('d-none');
            } else {
                $('#date-container').addClass('d-none');
            }
        });
</script>
@endpush
