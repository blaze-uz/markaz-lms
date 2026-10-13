<?php

namespace App\View\Components\ActionBtn;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ShowBtn extends Component
{
    public function __construct(
        public ?string $url = null,
    )
    {}

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.action-btn.show-btn');
    }
}
