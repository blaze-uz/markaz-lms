<?php

namespace Database\Seeders;

use App\Models\Settings;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            'name' => 'Markaz LMS',
            'address' => null,
            'phone' => null,
            'email' => null,
            'logo' => null,
            'send_sms' => 'enable',
        ];

        foreach ($settings as $key => $value) {
            if (! Settings::where('key', $key)->exists()) {
                Settings::create([
                    'key' => $key,
                    'value' => $value,
                ]);
            }
        }
    }
}
