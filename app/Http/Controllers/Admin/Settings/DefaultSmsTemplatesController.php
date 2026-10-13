<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\SmsTemplate;
use Illuminate\View\View;

class DefaultSmsTemplatesController extends Controller
{
    public function index(): View
    {
        $templates = SmsTemplate::query()->orderBy('id')->get();

        return view('admin.settings.sms.default-templates', compact('templates'));
    }
}
