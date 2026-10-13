<?php

namespace App\View\Components\ActionBtn;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Buttons extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public ?bool   $editable = true,
        public ?bool   $showable = false,
        public ?bool   $deletable = true,
        public ?string $editUrl = '',
        public ?string $showUrl = '',
        public ?int    $id = null,
        public ?string $deleteModalId = 'deleteRecordModal'
    )
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.action-btn.buttons');
    }
}
