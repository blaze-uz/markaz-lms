<?php

namespace App\Http\Livewire\Settings;

use App\Models\Setting;
use Livewire\Component;

class LoginLogoComponent extends Component
{
    public $logo;

    public function mount()
    {
        $this->logo = Setting::where('key', 'login_logo')->first()?->value;
    }

    public function render()
    {
        return view('livewire.settings.login-logo-component');
    }
}
