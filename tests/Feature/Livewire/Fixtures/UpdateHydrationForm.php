<?php

namespace Tests\Feature\Livewire\Fixtures;

use Livewire\Form;

class UpdateHydrationForm extends Form
{
    public string $name = '';

    public array $phones = [];

    /** @return array<string, string> */
    public function rules(): array
    {
        return ['name' => 'required|string', 'phones.*' => 'digits:9'];
    }
}
