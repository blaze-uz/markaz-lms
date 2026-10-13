<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public $service;

    public function __construct()
    {
        $this->service = new DashboardService();
    }

    public function getCalendarData()
    {
        $data = $this->service->getCalendarData();
        return response()->json($data);
    }

    public function calendar()
    {
        $resources = $this->service->getResources();
        return view('admin.calendar', compact('resources'));
    }

    public function birthdays()
    {
        return view('admin.birthdays.index');
    }

    public function setTemplate(Request $request)
    {
        $data = $request->validate([
            'template_id' => 'required|exists:sms_templates,id'
        ]);

        $this->service->setTemplate($data);
        session()->flash('success_alert', __("translation.alert_messages.updated"));
        return redirect()->back();
    }

    public function sendSms()
    {
        $birthday_sms_template = $this->service->getBirthdaySmsTemplateExists();
        if (!$birthday_sms_template) {
            session()->flash('error_alert', __("translation.alert_messages.birthday_sms_template_not_set"));
            return redirect()->back();
        }
        $this->service->sendBirthdaySms();
        session()->flash('success_alert', __("translation.alert_messages.birthday_sms_sent"));
        return redirect()->route('admin.sms.index');
    }
}
