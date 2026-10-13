<?php

namespace App\Http\Controllers;

use App\Services\SmsSettingsService;
use Illuminate\Http\Request;

class SmsSettingsController extends Controller
{
    protected $service;

    public function __construct(SmsSettingsService $service)
    {
        $this->service = $service;
    }

    /**
     * Handle debt SMS template settings
     */
    public function setDebtTemplate(Request $request)
    {
        $data = $request->validate([
            'selectedTemplateId' => 'nullable|exists:sms_templates,id',
            'autoSendEnabled' => 'nullable|boolean',
            'sendDay' => 'nullable|integer|min:1|max:31',
            'sendTime' => 'nullable|date_format:H:i',
            'sendType' => 'nullable|in:all,custom',
            'selectedGroups' => 'nullable|array',
            'selectedGroups.*' => 'integer|exists:groups,id',
        ]);

        // Map field names for the service
        if (isset($data['selectedTemplateId'])) {
            $data['debt_sms_template'] = $data['selectedTemplateId'];
            unset($data['selectedTemplateId']);
        }

        if (isset($data['autoSendEnabled'])) {
            $data['debt_auto_send_enabled'] = $data['autoSendEnabled'] ? '1' : '0';
            unset($data['autoSendEnabled']);
        }

        if (isset($data['sendDay'])) {
            $data['debt_sms_scheduled_day'] = $data['sendDay'];
            unset($data['sendDay']);
        }

        if (isset($data['sendTime'])) {
            $data['debt_sms_scheduled_time'] = $data['sendTime'];
            unset($data['sendTime']);
        }

        if (isset($data['sendType'])) {
            $data['debt_sms_send_type'] = $data['sendType'];
            unset($data['sendType']);
        }

        if (isset($data['selectedGroups'])) {
            $data['debt_sms_selected_groups'] = json_encode($data['selectedGroups']);
            unset($data['selectedGroups']);
        }

        $this->service->setDebtTemplate($data);
        session()->flash('success_alert', __('translation.alert_messages.updated'));
        return redirect()->route('admin.settings.sms');
    }
}
