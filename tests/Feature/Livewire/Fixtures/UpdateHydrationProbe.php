<?php

namespace Tests\Feature\Livewire\Fixtures;

use Illuminate\Support\Collection;
use Livewire\Component;

class UpdateHydrationProbe extends Component
{
    public UpdateHydrationForm $form;

    public Collection $tags;

    public array $filters = ['status' => ''];

    public array $saved = [];

    public function mount(): void
    {
        $this->tags = collect(['original']);
    }

    public function save(): void
    {
        $this->saved = $this->form->validate();
    }

    public function render(): string
    {
        return '<div></div>';
    }
}
