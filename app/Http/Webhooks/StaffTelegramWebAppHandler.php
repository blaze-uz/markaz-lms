<?php

namespace App\Http\Webhooks;

use App\Models\User;
use App\Models\UserTelegram;
use DefStudio\Telegraph\Handlers\WebhookHandler;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;
use Illuminate\Support\Stringable;

class StaffTelegramWebAppHandler extends WebhookHandler
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function deleteCallbackMessage(): void
    {
        if ($this->callbackQuery?->message()) {
            $this->chat->deleteMessage($this->callbackQuery->message()->id())->send();
        }
    }

    public function start(): void
    {
        $this->deleteCallbackMessage();
        $this->chat->state = null;
        $this->chat->save();

        if ($this->isRegistered()) {
            $this->showMainMenu();

            return;
        }

        $this->showRegistrationPrompt();
    }

    protected function handleChatMessage(Stringable $text): void
    {
        $this->deleteCallbackMessage();
        info('Current state: '.$this->chat->state);

        // If contact is shared, handle it regardless of state
        if ($this->message?->contact()) {
            info('Contact message received');
            $this->handleContact();

            return;
        }

        match ($this->chat->state) {
            'phone_request' => $this->handlePhoneSubmission($text),
            'registered' => $this->handleRegisteredUserMessage($text),
            default => $this->handleUnregisteredUserMessage($text),
        };
    }

    private function isRegistered(): bool
    {
        return UserTelegram::where('chat_id', $this->chat->chat_id)
            ->where('status', 'active')
            ->exists();
    }

    private function showMainMenu(): void
    {
        $this->chat->message('Xush kelibsiz!')
            ->withData('reply_markup', ['remove_keyboard' => true])
            ->keyboard(Keyboard::make()->buttons([
                Button::make('📱 Ilovani ochish')->webApp(route('inertia-teachers.index', ['telegram_id' => $this->chat->chat_id])),
            ]))
            ->send();
    }

    private function showRegistrationPrompt(): void
    {
        $this->requestPhoneNumber();
        $this->chat->state = 'phone_request';
        $this->chat->save();
    }

    protected function requestPhoneNumber(): void
    {
        $keyboard = [
            'keyboard' => [
                [
                    [
                        'text' => '📱 Telefon raqamni yuborish',
                        'request_contact' => true,
                    ],
                ],
            ],
            'resize_keyboard' => true,
            'one_time_keyboard' => true,
            'input_field_placeholder' => 'Telefon raqamingizni yuboring',
            'is_persistent' => false,
        ];

        $this->chat->message('Telefon raqamingizni yuborish uchun quyidagi tugmani bosing:')
            ->withData('reply_markup', json_encode($keyboard))
            ->send();
    }

    private function handleContact(): void
    {
        info('Processing contact');
        $contact = $this->message->contact();

        if (! $contact || ! $contact->phoneNumber()) {
            info('No valid contact found');
            $this->chat->message("❌ Telefon raqami topilmadi. Iltimos, qayta urinib ko'ring.")->send();
            $this->requestPhoneNumber();

            return;
        }

        $phoneNumber = $contact->phoneNumber();
        info('Valid contact found, processing phone: '.$phoneNumber);

        // Clean the phone number - remove all non-digits and any prefix
        $cleanPhone = preg_replace('/[^\d]/', '', $phoneNumber);

        // Remove country code if present
        if (strlen($cleanPhone) > 9 && str_starts_with($cleanPhone, '998')) {
            $cleanPhone = substr($cleanPhone, 3);
        }

        info('Cleaned phone number: '.$cleanPhone);

        // Try different phone number formats
        $phone1 = $cleanPhone; // Just the number without prefix (901234567)
        $phone2 = '998'.$cleanPhone; // With 998 prefix (998901234567)
        $phone3 = '+998'.$cleanPhone; // With +998 prefix (+998901234567)

        // Format like +998(90)123-45-67 - only if we have exactly 9 digits
        $phone4 = '';
        if (strlen($cleanPhone) === 9) {
            $phone4 = preg_replace('/(\d{2})(\d{3})(\d{2})(\d{2})/', '+998($1)$2-$3-$4', $cleanPhone);
        }

        info("Trying phone formats: {$phone1}, {$phone2}, {$phone3}, {$phone4}");

        // Find user by any phone number format
        $user = User::where(function ($query) use ($phone1, $phone2, $phone3, $phone4) {
            $query->where('phone', $phone1)
                ->orWhere('phone', $phone2)
                ->orWhere('phone', $phone3);
            if (! empty($phone4)) {
                $query->orWhere('phone', $phone4);
            }
        })->first();

        info('User found: '.($user ? 'yes (ID: '.$user->id.', Name: '.$user->name.')' : 'no'));

        if (! $user) {
            $this->chat->message("❌ Kechirasiz, bu raqam tizimda topilmadi. Iltimos, to'g'ri raqam kiriting yoki administrator bilan bog'laning.")->send();
            $this->requestPhoneNumber();

            return;
        }

        // Create or update UserTelegram record
        $userTelegram = $user->user_telegrams()->where('chat_id', $this->chat->chat_id)->first();

        if (! $userTelegram) {
            // Create new UserTelegram record
            $user->user_telegrams()->create([
                'chat_id' => $this->chat->chat_id,
                'status' => 'active',
            ]);
            info('Created new UserTelegram record for chat_id: '.$this->chat->chat_id);
        } else {
            // Update existing record to active status
            $userTelegram->update(['status' => 'active']);
            info('Updated existing UserTelegram record to active for chat_id: '.$this->chat->chat_id);
        }

        // Also update the user's telegram_id column for backward compatibility
        $user->update(['telegram_id' => $this->chat->chat_id]);
        info('Updated user telegram_id column: '.$this->chat->chat_id);

        // Update chat state
        $this->chat->state = 'registered';
        $this->chat->save();

        $this->chat->message("✅ Muvaffaqiyatli ro'yxatdan o'tdingiz!")->send();
        $this->showMainMenu();
    }

    private function handlePhoneSubmission(Stringable $text): void
    {
        $this->chat->message('Please use the Share Contact button below')->send();
    }

    private function handleRegisteredUserMessage(Stringable $text): void
    {
        match ($text) {
            '🏫 Attendance' => $this->showAttendanceMenu(),
            '📊 Reports' => $this->showReportsMenu(),
            '⚙️ Settings' => $this->showSettingsMenu(),
            default => $this->showMainMenu(),
        };
    }

    private function handleUnregisteredUserMessage(Stringable $text): void
    {
        $this->showRegistrationPrompt();
    }

    private function showAttendanceMenu(): void
    {
        $keyboard = Keyboard::make()
            ->button('📝 Take Attendance')
            ->button('📋 View Attendance')
            ->button('🔙 Back');

        $this->chat->message('Attendance Menu:')->keyboard($keyboard)->send();
    }

    private function showReportsMenu(): void
    {
        $keyboard = Keyboard::make()
            ->button('📈 Daily Report')
            ->button('📊 Monthly Report')
            ->button('🔙 Back');

        $this->chat->message('Reports Menu:')->keyboard($keyboard)->send();
    }

    private function showSettingsMenu(): void
    {
        $keyboard = Keyboard::make()
            ->button('🔔 Notifications')
            ->button('👤 Profile')
            ->button('🔙 Back');

        $this->chat->message('Settings Menu:')->keyboard($keyboard)->send();
    }
}
