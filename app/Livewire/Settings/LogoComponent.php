<?php

namespace App\Livewire\Settings;

use App\Models\Settings;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class LogoComponent extends Component
{
    public $logo;

    public function mount(): void
    {
        $this->getData();
    }

    public function render()
    {
        return view('livewire.settings.logo-component');
    }

    private function getData(): void
    {
        $this->logo = Cache::remember('logo_url', now()->addHours(1), function () {
            $settings = Settings::where('key', 'logo')->first();
            return $settings ? $settings->getFirstMediaUrl() : null;
        });
    }
}
