<div class="list-group-item nested-1" data-id="{{ $menu->id }}">
    <i class="{{ $menu->icon }} fs-16 align-middle text-primary me-2"></i> {{ $menu->name }}
    @if($menu->children->isNotEmpty())
        <div class="list-group nested-list nested-sortable">
            @foreach($menu->children as $child)
                @include('admin.settings.menu-settings.menu-item', ['menu' => $child])
            @endforeach
        </div>
    @endif
</div>
