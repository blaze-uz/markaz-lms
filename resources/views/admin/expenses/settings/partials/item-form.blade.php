<x-input
    name="name"
    label="{{__('translation.expenses.item_name')}}"
    :value="$item->name ?? old('name') ?? ''"
/>
<div class="mb-3">
    <label for="description">
        @lang('translation.expenses.item_description')
    </label>
    <textarea name="description" id="description" cols="10" rows="5"
              class="form-control">{{$item->description ?? old('description')}}</textarea>
    @error('description')
    <div class="text-danger">
        {{$message}}
    </div>
    @enderror
</div>
