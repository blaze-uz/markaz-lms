<?php

namespace App\View\Components\ActionBtn;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class DeleteBtn extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public ?int $id = null,
        public string $modalId = 'deleteRecordModal'
    )
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.action-btn.delete-btn');
    }
}
