<?php

namespace App\Http\Controllers;

use App\Services\ValidationSettingsService;
use Illuminate\Http\Request;

class ValidationSettingsController extends Controller
{
    public $service;

    public function __construct()
    {
        $this->service = new ValidationSettingsService();
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'group' => 'required',
        ]);

        $this->service->update($data);

        session()->flash("success_alert", __("translation.alert_messages.updated"));
        return redirect()->back();
    }
}
