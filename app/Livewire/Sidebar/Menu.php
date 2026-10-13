<?php

namespace App\Livewire\Sidebar;

use App\Models\Menu as MenuModel;
use Livewire\Component;

class Menu extends Component
{
    public function render()
    {
        $userId = auth()->id();
        $cacheKey = "user_{$userId}_menu_items";

        $menu_items = cache()->rememberForever($cacheKey, function () use ($userId) {
            $userMenuItems = auth()->user()->menus()->whereNull('parent_id')->orderBy('order')->get();
            if ($userMenuItems->isEmpty()) {
                return MenuModel::with(['children', 'users'])->whereNull('parent_id')->orderBy('order')->get();
            } else {
                return $userMenuItems->sortBy('pivot.order');
            }
        });

        return view('livewire.sidebar.menu', compact('menu_items'));
    }
}
