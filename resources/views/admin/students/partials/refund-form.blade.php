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

    <x-input :label="__('translation.students.full_name')"
             name="name"
             :placeholder="__('translation.students.name')"
             attribute="disabled"
             value="{{$item->getFullName()}} | {{$item->getFormattedPhone()}}"
    />
    <div class="form-group mb-3">
        <label for="payment-type">
            @lang("translation.students.payment_type")
        </label>
        <select class="form-control @error('payment_type_id') is-invalid @enderror" id="payment-type"
                name="payment_type_id">
            @foreach($paymentTypes as $paymentType)
                <option value="{{$paymentType->id}}"
                        @if($item->payment_type_id == $paymentType->id) selected @endif>
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
    <div class="form-group mb-3">
        <label for="payment-type">
            @lang("translation.students.group")
        </label>
        <select class="form-control @error('group_id') is-invalid @enderror" id="payment-type"
                name="group_id">
            <option value="">@lang("translation.groups.choose_group")</option>
            @foreach($item->groups as $group)
                <option value="{{$group->id}}">
                    {{$group->name}}
                </option>
            @endforeach
        </select>
        @error('group_id')
        <div class="invalid-feedback">
            {{$message}}
        </div>
        @enderror
    </div>
    <x-money-input
        :label="__('translation.students.payment_amount')"
        name="amount"
        :placeholder="__('translation.students.payment_amount')"
        :value="old('amount') ?? ''"
    />
    <!-- date switcher -->
    <div class="form-check form-switch mb-3">
        <input class="form-check-input" type="checkbox" role="switch" id="refundDateSwitch">
        <label class="form-check-label" for="refundDateSwitch">
            @lang("translation.students.payment_date_switch")
        </label>
    </div>
    <div class="form-group mb-3" id="date-container-refund">
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
                $('#date-container-refund').hide();
                $('#refundDateSwitch').change(function () {
                    if ($(this).is(':checked')) {
                        $('#date-container-refund').show();
                    } else {
                        $('#date-container-refund').hide();
                    }
                });
            });
        </script>
    @endpush
</div>
