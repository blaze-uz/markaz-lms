<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserTelegramController
{
    private $service;

    public function __construct()
    {
        $this->service = new \App\Services\UserTelegramService();
    }

    public function sendTelegramMessage(Request $request, $id)
    {
        $data = $request->validate([
            'message' => 'required',
        ]);
        $this->service->sendTelegramMessage($data['message'], $id);
        session()->flash("success_alert", __("translation.alert_messages.telegram_sent"));
        return redirect()->route("admin.users.show", ["user" => $id]);
    }

    public function sendTelegramToMultiple(Request $request)
    {
        $data = $request->validate([
            'message' => 'required|string',
            'user_ids' => 'required|array',
            'user_ids.*' => 'required|exists:users,id',
        ]);

        $userIds = $data['user_ids'];
        $message = $data['message'];
        $sentCount = 0;
        $failedCount = 0;

        foreach ($userIds as $userId) {
            try {
                $this->service->sendTelegramMessage($message, $userId);
                $sentCount++;
            } catch (\Exception $e) {
                $failedCount++;
            }
        }

        if ($sentCount > 0) {
            session()->flash("success_alert", __("translation.alert_messages.telegram_sent") . " ({$sentCount})");
        }
        if ($failedCount > 0) {
            session()->flash("error_alert", __("translation.alert_messages.error") . " ({$failedCount})");
        }

        return redirect()->route("admin.users.index");
    }
}
