<?php

namespace App\Services;

use App\Models\ValidationSettings;
use Illuminate\Support\Facades\DB;

class ValidationSettingsService
{
    public function update(array $data)
    {
        DB::beginTransaction();
        try {
            $validationSettings = ValidationSettings::where('group', $data['group'])->get();

            $updatedSettings = request()->value ?? [];

            foreach ($validationSettings as $setting) {
                $isRequired = array_key_exists($setting->name, $updatedSettings);
                $setting->update(['required' => $isRequired]);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        DB::commit();
    }
}
