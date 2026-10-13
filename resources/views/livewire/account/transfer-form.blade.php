<div>
    @push('css')
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
    @endpush
    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <label for="from_account">@lang("attr.from_account")</label>
                <select name="from_account_id" id="from_account" class="form-control select2 form-select">
                    <option value="" disabled selected>@lang("translation.choose")</option>
                    @foreach($items as $item)
                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                    @endforeach
                </select>
                @error('from_account_id')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label for="to_account">@lang("attr.to_account")</label>
                <select name="to_account_id" id="to_account" class="form-control select2 form-select">
                    <option value="" disabled selected>@lang("translation.choose")</option>
                    @foreach($items as $item)
                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                    @endforeach
                </select>
                @error('to_account_id')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <x-money-input name="amount" :label="__('translation.transfers.amount')" value="" id="amount"/>
                @error('amount')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
            </div>
        </div>
    </div>
    @push('custom-scripts')
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                $('.select2').select2();

                {{--$('.select2').on('change', function (e) {--}}
                {{--    --}}{{--var data = $('#select2-dropdown').select2("val");--}}
                {{--    --}}{{--@this.--}}
                {{--    --}}{{--set('selectedItem', data);--}}
                {{--});--}}

                // window.livewire.on('select2', data => {
                //     $('#select2-dropdown').val(data).trigger('change');
                // });
            });
        </script>
    @endpush
</div>
