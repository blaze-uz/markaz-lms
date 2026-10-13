<div class="form-group mb-3">
    <x-money-input :label="__('attr.amount')" name="amount" :value="old('amount') ?? ''"/>
    @error('amount')
    <div class="text-danger">{{ $message }}</div>
    @enderror
</div>
<div class="form-group mb-3">
    <label for="description">
        {{ __('attr.description') }}
    </label>
    <textarea class="form-control" name="description" id="description"
              rows="3">{{ old('description') ?? '' }}</textarea>
</div>
<div class="form-check form-switch mb-3">
    <input class="form-check-input" type="checkbox" role="switch" id="changeDateSwitch">
    <label class="form-check-label" for="changeDateSwitch">
        @lang("translation.users.change_date")
    </label>
</div>
<div class="form-group mb-3 d-none" id="dateGroup">
    <label for="date">
        @lang("attr.date")
    </label>
    <input type="date" class="form-control" name="date" id="date" value="{{ old('date') ?? '' }}">
    @error('date')
    <div class="text-danger">{{ $message }}</div>
    @enderror
</div>
@push('custom-scripts')
    <script>
        const changeDateSwitch = document.getElementById('changeDateSwitch');
        const dateGroup = document.getElementById('dateGroup');
        changeDateSwitch.addEventListener('change', function () {
            dateGroup.classList.toggle('d-none');
            document.getElementById('date').value = '';
        });
    </script>
@endpush