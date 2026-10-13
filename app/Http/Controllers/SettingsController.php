<?php

namespace App\Http\Controllers;

use App\Http\Requests\Settings\SettingsRequest;
use App\Models\PaymentType;
use App\Models\Settings;
use App\Models\SmsTemplate;
use App\Services\LeadDynamicFieldService;
use App\Services\SettingsService;
use App\Services\StaffTelegramBotService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SettingsController extends Controller
{
    public SettingsService $service;

    public function __construct(
        protected StaffTelegramBotService $staffTelegramBotService,
        SettingsService $service,
        protected LeadDynamicFieldService $leadDynamicFieldService,
    ) {
        $this->service = $service;
    }

    public function index()
    {
        return view('admin.settings.index');
    }

    public function generalSettingsIndex()
    {
        $settings = Settings::all();
        $data = [];
        foreach ($settings as $setting) {
            $data[$setting->key] = $setting->value;
        }
        $settings = (object) $data;

        return view('admin.settings.general.index', compact('settings'));
    }

    public function generalSettingsUpdate(SettingsRequest $request)
    {
        $this->service->update($request->validated());
        session()->flash('success_alert', __('translation.alert_messages.settings_updated'));

        return redirect()->back();
    }

    public function paymentTypes()
    {
        $paymentTypes = PaymentType::orderBy('position')->get();

        return view('admin.settings.payment-types.index', compact('paymentTypes'));
    }

    public function createPaymentType()
    {
        return view('admin.settings.payment-types.create');
    }

    public function storePaymentType(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
        ]);
        PaymentType::create($data);
        session()->flash('success_alert', __('translation.alert_messages.payment_type_created'));

        return redirect()->route('admin.settings.payment-types');
    }

    public function editPaymentType(PaymentType $paymentType)
    {
        return view('admin.settings.payment-types.edit', compact('paymentType'));
    }

    public function updatePaymentType(Request $request, PaymentType $paymentType)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
        ]);
        $paymentType->update($data);
        session()->flash('success_alert', __('translation.alert_messages.payment_type_updated'));

        return redirect()->route('admin.settings.payment-types');
    }

    public function deletePaymentType(PaymentType $paymentType)
    {
        $paymentType->delete();
        session()->flash('success_alert', __('translation.alert_messages.payment_type_deleted'));

        return redirect()->route('admin.settings.payment-types');
    }

    public function paymentTypesReorder()
    {
        return view('admin.settings.payment-types.reorder');
    }

    public function updatePaymentTypesReorder(Request $request)
    {
        $data = $request->validate([
            'orders' => 'required|array',
        ]);
        $this->service->reorderPaymentTypes($data['orders']);

        return response()->json(['success' => true]);
    }

    public function darkMode()
    {
        if (! session()->has('dark_mode')) {
            session()->put('dark_mode', true);
        } elseif (session()->has('dark_mode') && session()->get('dark_mode')) {
            session()->put('dark_mode', false);
        } else {
            session()->put('dark_mode', true);
        }

        return redirect()->back();
    }

    public function sms()
    {
        return view('admin.settings.sms.index');
    }

    public function leads()
    {
        // Get lead settings
        $lead = Settings::firstOrCreate(
            ['key' => 'leads_page'],
            ['value' => 'board']
        );

        // Check if API key exists, if not, generate a new one
        $apiKey = Settings::firstOrCreate(
            ['key' => 'leads_api_key'],
            ['value' => Str::random(32)] // Generate a random 32-character API key
        );

        $leadDynamicFields = $this->leadDynamicFieldService->definitions();
        foreach ($leadDynamicFields as $i => $row) {
            $leadDynamicFields[$i]['options_raw'] = implode("\n", $row['options']);
        }

        return view('admin.settings.leads.index', [
            'lead' => $lead,
            'apiKey' => $apiKey,
            'leadDynamicFields' => $leadDynamicFields,
        ]);
    }

    public function leadsUpdate(Request $request)
    {
        // Validate request
        $request->validate([
            'leads_page' => 'required|in:board,table',
            'regenerate_api_key' => 'nullable|boolean',
        ]);

        $encoded = $this->leadDynamicFieldService->validateAndEncodeDefinitions(
            $request->input('dynamic_fields', [])
        );

        Settings::updateOrCreate(
            ['key' => LeadDynamicFieldService::SETTINGS_KEY],
            ['value' => $encoded]
        );

        // Update leads page setting
        Settings::updateOrCreate(
            ['key' => 'leads_page'],
            ['value' => $request->leads_page]
        );

        // Clear cache for leads_page setting
        cache()->forget('leads_page');

        // Check if we need to regenerate API key
        if ($request->has('regenerate_api_key') && $request->regenerate_api_key) {
            $newApiKey = Str::random(32);
            Settings::updateOrCreate(
                ['key' => 'leads_api_key'],
                ['value' => $newApiKey]
            );
            cache()->forget('settings.leads_api_key'); // Clear cache for the new API key

            return redirect()
                ->route('admin.settings.leads')
                ->with('success', __('translation.alert_messages.updated'));
        }

        return redirect()
            ->route('admin.settings.leads')
            ->with('success', __('translation.alert_messages.updated'));
    }

    public function setTemplate(Request $request)
    {
        $data = $request->validate([
            'attendance_notice_template' => 'required',
        ]);
        $this->service->setTemplate($data);
        session()->flash('success_alert', __('translation.alert_messages.updated'));

        return redirect()->route('admin.settings.sms');
    }

    public function setPaymentTemplate(Request $request)
    {
        $data = $request->validate([
            'payment_sms_template' => 'nullable',
            'payment_sms_status' => 'nullable',
        ]);
        $this->service->setPaymentTemplate($data);
        session()->flash('success_alert', __('translation.alert_messages.updated'));

        return redirect()->route('admin.settings.sms');
    }

    public function telegram()
    {
        $telegram_bot_token = Settings::where('key', 'telegram_bot_token')->first()?->value;

        return view('admin.settings.telegram.index', [
            'telegram_bot_token' => $telegram_bot_token,
        ]);
    }

    public function telegramBot(Request $request)
    {
        $data = $request->validate([
            'telegram_bot_token' => 'required',
        ]);
        $this->service->setTelegramBotToken($data['telegram_bot_token']);
        session()->flash('success_alert', __('translation.alert_messages.updated'));

        return redirect()->route('admin.settings.telegram');
    }

    public function groupSettings()
    {
        $templates = SmsTemplate::orderBy('name')->select('id', 'name')->get();
        $attendanceNoticeTemplate = Settings::where('key', 'attendance_notice_template')->first();
        $attendanceSmsStatus = Settings::where('key', 'attendance_notice_status')->first();

        return view('admin.settings.group-settings.index', [
            'templates' => $templates,
            'attendanceNoticeTemplate' => $attendanceNoticeTemplate,
            'attendanceSmsStatus' => $attendanceSmsStatus,
        ]);
    }

    public function groupAttendanceUpdate(Request $request)
    {
        $data = $request->validate([
            'attendance_notice_template' => 'required',
            'attendance_notice_status' => 'sometimes',
        ]);

        $this->service->setGroupAttendanceSettings($data);
        session()->flash('success_alert', __('translation.alert_messages.updated'));

        return redirect()->route('admin.settings.group-settings');
    }

    public function menuSettingsView()
    {
        $menu_items = $this->service->getMenuItems();

        return view('admin.settings.menu-settings.index', [
            'menu_items' => $menu_items,
        ]);
    }

    public function staff()
    {
        $bot_token = Settings::where('key', 'staff_telegram_bot_token')->first()?->value;

        return view('admin.settings.staff.index', compact('bot_token'));
    }

    public function updateStaffBotToken(Request $request)
    {
        $request->validate([
            'bot_token' => 'required|string|min:45|max:55',
        ]);

        try {
            $this->staffTelegramBotService->updateToken($request->all());

            return redirect()
                ->back()
                ->with('success_alert', __('translation.settings_saved'));
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Handle validation errors from the service
            $errorMessage = collect($e->errors())->flatten()->first();

            return redirect()
                ->back()
                ->withInput()
                ->with('error_alert', $errorMessage);
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error_alert', $e->getMessage());
        }
    }

    public function menuSettingsUpdate(Request $request)
    {
        $menuData = json_decode($request->input('menu'), true);

        if ($menuData) {
            $this->service->updateMenuItems($menuData);
        }

        session()->flash('success_alert', __('translation.alert_messages.menu_updated'));

        return redirect()->route('admin.settings.menu-settings-view');
    }

    public function updateLogo(Request $request)
    {
        $this->service->updateLogo($request->file('file'));
        session()->flash('success_alert', __('translation.alert_messages.updated'));

        return redirect()->back();
    }

    public function staffSettingsView()
    {
        $bot_token = Settings::where('key', 'staff_telegram_bot_token')->first()?->value;

        return view('admin.settings.staff.index', compact('bot_token'));
    }

    public function setTelegram(Request $request)
    {
        $data = $request->validate([
            'bot_token' => 'required|string|min:45|max:55',
        ]);

        try {
            $this->service->setTelegramBotToken($data['bot_token']);
            session()->flash('success_alert', __('translation.alert_messages.updated'));

            return redirect()->route('admin.settings.staff-settings-view');
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();

            \Log::error($errorMessage);

            // Provide more user-friendly error messages for common issues
            if (str_contains($errorMessage, 'webhook') || str_contains($errorMessage, 'Webhook')) {
                $errorMessage = __('Failed to register webhook. Please check your bot token and server configuration.');
            } elseif (str_contains($errorMessage, 'token') || str_contains($errorMessage, 'Token')) {
                $errorMessage = __('Invalid bot token. Please verify your bot token is correct.');
            } elseif (str_contains($errorMessage, 'curl') || str_contains($errorMessage, 'SSL') || str_contains($errorMessage, 'network')) {
                $errorMessage = __('Network error. Please check your internet connection and try again.');
            }

            session()->flash('error_alert', $errorMessage);

            return redirect()
                ->back()
                ->withInput();
        }
    }
}
