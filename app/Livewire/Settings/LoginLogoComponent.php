<?php

namespace App\Livewire\Settings;

use App\Models\Settings;
use Livewire\Component;

class LoginLogoComponent extends Component
{
    public function render()
    {
        $logoSetting = Settings::firstWhere('key', 'logo');
        $logo = $logoSetting?->getFirstMediaUrl();
        return view('livewire.settings.login-logo-component', compact('logo'));
    }
}
