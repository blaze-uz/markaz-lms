<x-input
    name="name"
    label="{{__('translation.expenses.place_name')}}"
    :value="$item->name ?? old('name') ?? ''"
/>
<div class="mb-3">
    <label for="description">
        @lang('translation.expenses.place_description')
    </label>
    <textarea name="description" id="description" cols="10" rows="5"
              class="form-control">{{$item->description ?? old('description')}}</textarea>
    @error('description')
    <div class="text-danger">
        {{$message}}
    </div>
    @enderror
</div>
