<?php

namespace App\Services;

use App\Models\Menu;
use App\Models\PaymentType;
use App\Models\Settings;
use DefStudio\Telegraph\Models\TelegraphBot;
use Illuminate\Support\Facades\DB;

class SettingsService
{
    public function update(array $data): void
    {
        foreach ($data as $key => $value) {
            $setting = Settings::where('key', $key)->first();

            if ($key === 'logo') {
                $setting->clearMediaCollection()->addMedia(request()->file('logo'))->toMediaCollection();
            }
            cache()->forget('payment_sms_status');
            cache()->forget('daily_review_command_run_time');
            cache()->forget('logo_url');
            $setting->update(['value' => $value]);
        }
    }

    public function reorderPaymentTypes(mixed $orders): void
    {
        foreach ($orders as $order) {
            $id = $order[0];
            $order = $order[1];
            PaymentType::find($id)->update(['position' => $order]);
        }
    }

    public function setTemplate(array $data)
    {
        $setting = Settings::where('key', 'attendance_notice_template')->first();
        $setting->update(['value' => $data['attendance_notice_template']]);

        cache()->forget('attendance_notice_template');
    }

    public function setPaymentTemplate(array $data)
    {
        // Update payment notice template
        if (isset($data['payment_sms_template'])) {
            Settings::updateOrCreate(
                ['key' => 'payment_sms_template'],
                ['value' => $data['payment_sms_template']]
            );
            cache()->forget('payment_sms_template');
        }

        // Update payment SMS status
        $paymentSmsStatus = isset($data['payment_sms_status']) ? '1' : '0';
        Settings::updateOrCreate(
            ['key' => 'payment_sms_status'],
            ['value' => $paymentSmsStatus]
        );
        cache()->forget('payment_sms_status');
    }


    public function setTelegramBotToken(mixed $telegram_bot_token): void
    {
        DB::beginTransaction();
        try {
            // First validate the bot token by trying to register webhook
            $this->registerBot($telegram_bot_token);

            // Only save to database if bot token is valid
            $setting = Settings::where('key', 'staff_telegram_bot_token')->first();
            if ($setting) {
                $setting->update(['value' => $telegram_bot_token]);
            } else {
                Settings::create([
                    'key' => 'staff_telegram_bot_token',
                    'value' => $telegram_bot_token,
                ]);
            }

            // Clean up any existing bots with this token (except the one we just created)
            $existingBots = TelegraphBot::where('token', $telegram_bot_token)
                ->where('name', '!=', 'Staff app')
                ->get();

            foreach ($existingBots as $existingBot) {
                try {
                    $existingBot->unregisterWebhook()->send();
                } catch (\Exception $e) {
                    // Ignore errors when unregistering old webhooks
                }
                $existingBot->delete();
            }

            //forget cache
            cache()->forget('settings.telegram_bot_token');
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function registerBot($telegram_bot_token)
    {
        // Delete any existing bot with the same name
        TelegraphBot::where('name', 'Staff app')->delete();

        $bot = TelegraphBot::create([
            'token' => $telegram_bot_token,
            'name' => 'Staff app',
        ]);

        // Try to register webhook and validate the response
        try {
            // Register webhook (domain is configured in config/telegraph.php)
            $response = $bot->registerWebhook()->send();

            // Check if the response indicates an error
            if (!$response->successful()) {
                // Delete the bot since it's invalid
                $bot->delete();

                $statusCode = $response->status();
                $responseBody = $response->body();

                // Try to decode the JSON response to get the actual error message
                $decodedResponse = json_decode($responseBody, true);
                $telegramErrorMessage = $decodedResponse['description'] ?? 'Unknown error';

                $errorMessage = 'Webhook registration failed';

                if ($statusCode === 400) {
                    $errorMessage = "Webhook setup error: {$telegramErrorMessage}. Please check your server configuration and ensure it's accessible via HTTPS.";
                } elseif ($statusCode === 404) {
                    $errorMessage = 'Bot token not found. Please verify your bot token is correct.';
                } elseif ($statusCode === 401) {
                    $errorMessage = 'Unauthorized bot token. Please check your bot token.';
                } elseif ($statusCode >= 500) {
                    $errorMessage = 'Telegram server error. Please try again later.';
                } else {
                    $errorMessage = "Telegram API error ({$statusCode}): {$telegramErrorMessage}";
                }

                throw new \Exception($errorMessage);
            }
        } catch (\Exception $e) {
            // Delete the bot if webhook registration fails
            $bot->delete();

            // If it's our custom exception, re-throw it
            if (strpos($e->getMessage(), 'Webhook') !== false || strpos($e->getMessage(), 'Bot token') !== false) {
                throw $e;
            }

            // For other exceptions (network, etc.), provide a helpful message
            throw new \Exception('Failed to connect to Telegram servers. Please check your internet connection and server configuration.');
        }

        return $bot;
    }

    public function setGroupAttendanceSettings(array $data): void
    {
        $attendance_notice_status = 'disable';
        if (isset($data['attendance_notice_status'])) {
            if ($data['attendance_notice_status'] === 'on') {
                $attendance_notice_status = 'enable';
            }
        }

        //update attendance_notice_status
        $setting = Settings::where('key', 'attendance_notice_status')->first();
        $setting->update([
            'value' => $attendance_notice_status,
        ]);

        //update attendance_notice_template
        $setting = Settings::where('key', 'attendance_notice_template')->first();
        $setting->update([
            'value' => $data['attendance_notice_template'],
        ]);

        cache()->forget('attendance_notice_status');
        cache()->forget('attendance_notice_template');
    }

    public function getMenuItems()
    {
        $menu_items = auth()->user()->menus()
            ->with('children')
            ->whereNull('parent_id')
            ->orderBy('order')
            ->get();

        if ($menu_items->isEmpty()) {
            $menu_items = Menu::with('children')
                ->whereNull('parent_id')
                ->orderBy('order')
                ->get();
        } else {
            $menu_items = $menu_items->sortBy('pivot.order');
        }

        return $menu_items;
    }

    public function updateMenuItems(array $menuData): void
    {
        DB::beginTransaction();
        try {
            $user = auth()->user();
            // Detach all existing menu items once before the loop
            $user->menus()->detach();
            foreach ($menuData as $key => $menu) {
                $this->updateMenuItem($menu, $key);
            }
            //forget cache
            cache()->forget('user_' . $user->id . '_menu_items');
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
        DB::commit();
    }

    private function updateMenuItem(mixed $menu, int|string $key): void
    {
        $user = auth()->user();
        $menuItem = Menu::find($menu['id']);
        $user->menus()->attach($menuItem->id, ['order' => $key]);

        if (isset($menu['children'])) {
            foreach ($menu['children'] as $childKey => $child) {
                $this->updateMenuItem($child, $childKey);
            }
        }
    }

    public function updateLogo(mixed $logo): void
    {
        $setting = Settings::where('key', 'logo')->first();
        $setting->clearMediaCollection()->addMedia($logo)->toMediaCollection();
        cache()->forget('logo_url');
        $setting->update(['value' => $setting->getFirstMediaUrl()]);
    }
}
