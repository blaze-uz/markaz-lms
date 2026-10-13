<div class="card-body">
    <div class="row">
        <div class="col-md-6 col-sm-12">
            <x-input :label="__('translation.courses.name')" name="name" :placeholder="__('translation.courses.name')" :value="$item->name ?? (old('name') ?? '')" />
        </div>
        {{-- <div class="col-md-6 col-sm-12">
            <x-money-input :label="__('translation.courses.price')" name="price" :placeholder="__('translation.courses.price')" :value="$item->price ?? (old('price') ?? '')" />
        </div> --}}
        <div class="col-md-6 col-sm-12">
            <x-input :label="__('translation.courses.duration')" type="number" name="duration" :placeholder="__('translation.courses.duration')" :value="$item->duration ?? (old('duration') ?? '')" />
        </div>
        <div class="col-md-6 col-sm-12">
            <label for="branches">
                @lang('translation.courses.choose_branches') <span class="text-danger">*</span>
            </label>
            <select name="branches[]" id="branches" class="form-control" multiple>
                @foreach ($branches as $key => $branch)
                    <option value="{{ $branch->id }}" @if (isset($item) && $item->branches->contains($branch->id)) selected @endif
                        @if (!isset($item) && old('branches') && in_array($branch->id, old('branches'))) selected @endif
                        @if (!isset($item) && !old('branches') && $key == 0) selected @endif>
                        {{ $branch->name }}
                    </option>
                @endforeach
            </select>
            @error('branches')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
            @error('branches.*')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-12 col-sm-12">
            <x-text-area :label="__('translation.courses.description')" name="description" :value="$item->description ?? (old('description') ?? '')" />
        </div>
    </div>
</div>

@section('script-bottom')
    <!--select2 cdn-->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#branches').select2({
                placeholder: "@lang('translation.courses.choose_branches')",
                allowClear: true,
                multiple: true,
                closeOnSelect: false,
            });
        });
    </script>
@endsection
