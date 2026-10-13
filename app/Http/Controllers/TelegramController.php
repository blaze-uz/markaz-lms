<?php

namespace App\Http\Controllers;

use App\Services\TelegramService;
use Illuminate\Http\Request;

class TelegramController extends Controller
{
    public $service;

    public function __construct()
    {
        $this->service = new TelegramService();
    }

    public function create()
    {
        return view('admin.telegram.create');
    }

    public function messagesIndex()
    {
        return view('admin.telegram.messages.index');
    }

    public function sendMessage(Request $request)
    {
        $telegram_token_status = $this->checkForTelegramToken();
        if (! $telegram_token_status) {
            session()->flash('error_alert', __('translation.telegram.no_token'));

            return redirect()->back();
        }

        $receivers = $this->service->getReceivers($request);

        return view('admin.telegram.send', [
            'receivers' => $receivers,
        ]);
    }

    private function checkForTelegramToken(): bool
    {
        $token = settings('student_telegram_bot_token');

        return ! empty($token);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'telegram_id' => 'required',
        ]);

        $this->service->create($data);
        session()->flash('success_alert', __('translation.alert_messages.created'));

        return redirect()->route('admin.settings.telegram');
    }

    public function destroy($id)
    {
        $this->service->delete($id);
        session()->flash('success_alert', __('translation.alert_messages.deleted'));

        return redirect()->route('admin.settings.telegram');
    }

    public function setDailyReport(Request $request)
    {
        $data = $request->validate([
            'telegram_ids' => 'sometimes',
            'status' => 'sometimes',
            'time' => 'required',
        ]);

        $this->service->setDailyReport($data);
        session()->flash('success_alert', __('translation.alert_messages.updated'));

        return redirect()->route('admin.settings.telegram');
    }

    public function deleteDailyReportItem($telegram_id)
    {
        $this->service->deleteDailyReportItem($telegram_id);
        session()->flash('success_alert', __('translation.alert_messages.deleted'));

        return redirect()->route('admin.settings.telegram');
    }

    public function send(Request $request)
    {
        $data = $request->validate([
            'date' => 'required',
        ]);
        $this->service->send($data);
        session()->flash('success_alert', __('translation.alert_messages.sent'));

        return redirect()->route('admin.settings.telegram');
    }
}
