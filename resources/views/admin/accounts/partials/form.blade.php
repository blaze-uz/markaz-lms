@section("css")
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
    <link href="{{ asset("assets/libs/flatpickr/flatpickr.min.css") }}" rel="stylesheet" type="text/css"/>
@endsection

<div class="card-body" id="vue-app">
    <div class="row">
        <div class="col-12">
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <div>
                        @lang("translation.error")
                    </div>
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>

                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
        </div>
    </div>
    <div class="row" v-for="(item,index) in transfers" :key="'tr' + index">
        <div class="col-md-4">
            <div class="form-group mb-3">
                <label :for="'expense_account_id' + index">@lang("attr.from_account")</label>
                <select :name="'transfers[' + index + '][expense_account_id]'"
                        :id="'expense_account_id' + index"
                        class="select2 form-control" data-placeholder="@lang("translation.choose")">
                    <option></option>
                    @foreach($accounts as $account)
                        <option value="{{ $account->id }}">{{ $account->name }}
                            <small>({{ money($account->getBalance()) }})</small>
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group mb-3">
                <label :for="'income_account_id' + index">@lang("attr.to_account")</label>
                <select :name="'transfers[' + index + '][income_account_id]'"
                        data-placeholder="@lang("translation.choose")"
                        :id="'income_account_id' + index"
                        class="select2 form-control">
                    <option></option>
                    @foreach($accounts as $account)
                        <option value="{{ $account->id }}">{{ $account->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group mb-3">
                <label :for="'amount' + index">@lang("translation.transfers.amount")</label>
                <input type="tel" :name="'transfers[' + index + '][amount]'" :id="'amount' + index"
                       class="form-control amount-input"
                       placeholder="@lang("translation.transfers.amount")">
                @error('amount*')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
            </div>
        </div>
{{--        <div class="col-md-2">--}}
{{--            <div class="form-group mb-3">--}}
{{--                <label :for="'date' + index">@lang("translation.transfers.date")</label>--}}
{{--                <input type="date" :name="'transfers[' + index + '][date]'" :id="'date' + index"--}}
{{--                       class="form-control flatpicker-date"--}}
{{--                       placeholder="@lang("translation.transfers.date")"--}}
{{--                       value="{{date('Y-m-d')}}">--}}
{{--                @error('date')--}}
{{--                <div class="invalid-feedback">--}}
{{--                    {{ $message }}--}}
{{--                </div>--}}
{{--                @enderror--}}
{{--            </div>--}}
{{--        </div>--}}
        <div class="col-md-1 d-flex align-items-center">
            <button v-if="index == transfers.length - 1" type="button" class="btn btn-danger remove-field-btn mt-2"
                    @click="transfers.splice(index, 1)">
                <span class="mdi mdi-trash-can"></span>
            </button>
        </div>
    </div>

    <div class="mb-3">
        <button type="button" class="btn btn-success" @click="add()">
            <span class="mdi mdi-plus"></span> @lang("translation.transfers.add")
        </button>
    </div>
</div>

@section("script")

    <!--jquery cdn-->
    <script src="{{ asset('assets/js/jquery-3.7.1.min.js') }}"></script>

    <!--select2 cdn-->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="{{ asset("assets/libs/flatpickr/flatpickr.min.js") }}"></script>
    <script src="{{ asset("assets/js/vue.global.js") }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.15/jquery.mask.js"></script>

    <script>
        const {createApp} = Vue

        createApp({
            data() {
                return {
                    transfers: [
                        {
                            expense_account_id: '',
                            income_account_id: '',
                            amount: '',
                            date: ''
                        }
                    ]
                }
            },
            mounted() {
                this.reRenderHtml()
            },
            methods: {
                add() {
                    this.transfers.push({
                        expense_account_id: '',
                        income_account_id: '',
                        amount: '',
                        date: ''
                    })

                    this.$nextTick(() => {
                        this.reRenderHtml()
                    })
                },
                reRenderHtml() {
                    $('.select2').select2();
                    $('.amount-input').mask('000,000,000,000', {reverse: true});
                    flatpickr(".flatpicker-date", {
                        dateFormat: "Y-m-d"
                    });
                }
            }
        }).mount('#vue-app')
    </script>
@endsection
