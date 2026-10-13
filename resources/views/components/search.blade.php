<div class="{{$parentClass ?? "my-3"}}">
    <input class="form-control {{$class ?? ''}}"
           style="{{$style ?? ''}}"
           type="search"
           placeholder="{{__('translation.search')}}"
           aria-label="{{__('translation.search')}}"
           wire:model.live.debounce.500ms="search">
</div>
