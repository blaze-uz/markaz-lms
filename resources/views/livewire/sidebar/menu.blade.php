<ul class="navbar-nav" id="navbar-nav">
    <li class="menu-title"><span>@lang('translation.menu')</span></li>
    @foreach($menu_items as $item)
    @if($item->route)
    @if(count($item->permissions))
    @canany($item->permissions)
    <li class="nav-item">
        <a @class(['nav-link menu-link cursor-pointer','active'=> (request()->routeIs($item->menu_active_route))])
            href="{{ route($item->route) }}">
            <i class="{{$item->icon}}"></i>
            <span>@lang($item->name)</span>
        </a>
    </li>
    @endcanany
    @else
    <li class="nav-item">
        <a @class(['nav-link menu-link cursor-pointer','active'=> (request()->routeIs($item->menu_active_route))])
            href="{{ route($item->route) }}">
            <i class="{{$item->icon}}"></i>
            <span>@lang($item->name)</span>
        </a>
    </li>
    @endif
    @else
    @if(count($item->permissions))
    @canany($item->permissions)
    <li class="nav-item">
        <a @class(['nav-link menu-link ',' active'=> request()->routeIs($item->menu_active_route)])
            href="#sidebar{{$item->name}}" data-bs-toggle="collapse" role="button"
            aria-expanded="false" aria-controls="sidebar{{$item->name}}">
            <i class="{{$item->icon}}"></i>
            <span>@lang($item->name)</span>
        </a>
        <div class="collapse menu-dropdown" id="sidebar{{$item->name}}">
            <ul class="nav nav-sm flex-column">
                @foreach($item->children as $sub_item)
                @if($sub_item->route)
                @if(count($sub_item->permissions))
                @canany($sub_item->permissions)
                <li class="nav-item">
                    <a @class(['nav-link menu-link cursor-pointer','active'=>
                        (request()->routeIs($sub_item->menu_active_route))])
                        href="{{ route($sub_item->route) }}">
                        <span><i class="{{$sub_item->icon}}"></i> @lang($sub_item->name)</span>
                    </a>
                </li>
                @endcanany
                @else
                <li class="nav-item">
                    <a @class(['nav-link menu-link cursor-pointer','active'=>
                        (request()->routeIs($sub_item->menu_active_route))])
                        href="{{ route($sub_item->route) }}">
                        <span><i class="{{$sub_item->icon}}"></i> @lang($sub_item->name)</span>
                    </a>
                </li>
                @endif
                @endif
                @endforeach
            </ul>
        </div>
    </li>
    @endcanany
    @else
    <li class="nav-item">
        <a @class(['nav-link menu-link cursor-pointer','active'=> (request()->routeIs($item->menu_active_route))])
            href="{{ route($item->route) }}">
            <i class="{{$item->icon}}"></i>
            <span>@lang($item->name)</span>
        </a>
    </li>
    @endif
    @endif
    @endforeach
</ul>