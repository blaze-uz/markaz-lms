@props(['item'])

@if($item->state == 'demo')
    <span class="badge bg-success-subtle text-success">
        @lang("translation.groups.demo_group")
    </span>
@endif

@if($item->state == 'completed')
    <span class="badge bg-warning-subtle text-warning">
        @lang("translation.groups.completed")
    </span>
@endif

@if($item->state !== 'completed' && !$item->isGroupExpired() && $item->getLeftGroupTime()->diffInDays() <= 15)
    <span class="cursor-pointer badge bg-warning-subtle text-warning" 
          data-bs-toggle="tooltip" 
          title="{{__('translation.groups.near_to_end')}}">
        {{ $item->getLeftGroupTime()->diffForHumans() }}
    </span>
@endif
