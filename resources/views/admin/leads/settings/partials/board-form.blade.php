<x-input
        name="name"
        label="{{__('translation.leads.board_name')}}"
        :value="$item->name ?? old('name') ?? ''"
/>
<label for="success_message">
    {{__('translation.leads.success_message')}}
</label>
<textarea
        name="success_message"
        id="success_message"
        :value="$item->success_message ?? old('success_message') ?? ''"
        class="form-control"
/>{{$item->success_message ?? old('success_message') ?? ''}}</textarea>
<div class="form-group mb-3">
    <label for="collection_id">
        @lang('translation.leads.collection')
    </label>
    <select name="collection_id" id="collection_id" class="form-control @error('collection') is-invalid @enderror">
        @foreach($collections as $collection)
            <option value="{{ $collection->id }}"
                    @if(isset($item) && $item->kanban_collection_id == $collection->id) selected @endif>
                {{ $collection->name }}
            </option>
        @endforeach
    </select>
    @error('collection')
    <div class="invalid-feedback">
        {{ $message }}
    </div>
    @enderror
</div>
<div class="form-check form-switch mb-3">
    <input class="form-check-input" type="checkbox" role="switch"
           id="groupSwitch" wire:model="groupSwitch" name="groupSwitch"
           @if(isset($item) && $item->group_id) checked @endif>
    <label class="form-check-label" for="groupSwitch">
        @lang("translation.leads.attach_group")
    </label>
</div>
<div class="form-check form-switch mb-3">
    <input class="form-check-input" type="checkbox" role="switch"
           id="commentFormSwitch" name="form_comment_state"
           @if(isset($item) && $item->form_comment_state) checked @else checked @endif>
    <label class="form-check-label" for="commentFormSwitch">
        @lang("translation.leads.form_comment_state")
    </label>
</div>
{{--class="form-group mb-3 d-none" --}}
<div id="group-container" @class(['form-group mb-3', 'd-none' => !isset($item) || !$item->group_id])>
    <label for="groupId">
        @lang("translation.leads.select_group")
    </label>
    <select name="group_id" id="groupId" class="form-control" wire:model="groupId">
        <option value="">@lang("translation.leads.select_group")</option>
        @foreach($groups as $group)
            <option value="{{ $group->id }}" @if(isset($item) && $item->group_id == $group->id) selected @endif>
                {{ $group->name }}
            </option>
        @endforeach
    </select>
</div>
@push('custom-scripts')
    <script src="{{ asset('assets/js/jquery-3.7.1.min.js') }}"></script>
    <script>
        $(document).ready(function () {
            $('#groupSwitch').on('change', function () {
                if ($(this).is(':checked')) {
                    $('#group-container').removeClass('d-none');
                } else {
                    $('#group-container').addClass('d-none');
                    //clear selected group
                    $('#groupId').val('');
                }
            });
        });
    </script>
@endpush
