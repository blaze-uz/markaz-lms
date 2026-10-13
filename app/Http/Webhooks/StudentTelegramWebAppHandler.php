<?php

namespace App\Http\Webhooks;

use App\Models\Student;
use DefStudio\Telegraph\Handlers\WebhookHandler;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;
use DefStudio\Telegraph\Models\TelegraphBot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Stringable;

class StudentTelegramWebAppHandler extends WebhookHandler
{
    public function __construct()
    {
        parent::__construct();
    }

    public function start(): void
    {
        info('StudentTelegramWebAppHandler: start() method called');
        try {
            $this->deleteCallbackMessage();
            $this->chat->state = null;
            $this->chat->save();

            if ($this->isRegistered()) {
                info('Student is registered, showing main menu');
                $this->showMainMenu();

                return;
            }

            info('Student is not registered, showing registration prompt');
            $this->showRegistrationPrompt();
        } catch (\Throwable $e) {
            info('start() method ERROR: '.$e->getMessage());
            Log::error('start() method exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
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

    protected function handleContact(): void
    {
        info('Processing contact');
        $contact = $this->message->contact();

        if (! $contact || ! $contact->phoneNumber()) {
            info('No valid contact found');
            $this->chat->message("❌ Telefon raqami topilmadi. Iltimos, qayta urinib ko'ring.")->send();
            $this->requestPhoneNumber();

            return;
        }

        // Any contact card can be sent; only the sender's own one, shared with
        // the button, proves the number is theirs.
        if ($contact->userId() === null || $contact->userId() !== $this->message->from()?->id()) {
            info('Contact is not the sender\'s own, ignoring it');
            $this->chat->message("❌ Iltimos, o'zingizning raqamingizni pastdagi tugma orqali yuboring.")->send();
            $this->requestPhoneNumber();

            return;
        }

        info('Valid contact found, processing phone: '.$contact->phoneNumber());
        $this->processPhoneNumber($contact->phoneNumber());
    }

    public function register(): void
    {
        $this->deleteCallbackMessage();

        if ($this->isRegistered()) {
            $this->chat->message("Siz allaqachon ro'yxatdan o'tgansiz!")->send();
            $this->showMainMenu();

            return;
        }

        info('Setting state to phone request');
        $this->chat->state = 'phone_request';
        $this->chat->save();
        $this->requestPhoneNumber();
    }

    protected function handlePhoneSubmission(Stringable $text): void
    {
        // A typed number says nothing about who owns it: binding a student by
        // it let anyone who knew a student's or parent's phone take over their
        // mini-app. Only the contact button binds.
        $this->chat->message('❌ Raqamni yozib yubormang, pastdagi «📱 Telefon raqamni yuborish» tugmasini bosing.')->send();
        $this->requestPhoneNumber();
    }

    protected function handleRegisteredUserMessage(Stringable $text): void
    {
        match ($text->toString()) {
            '/start' => $this->showMainMenu(),
            '/menu' => $this->showMainMenu(),
            default => $this->unknownCommand(),
        };
    }

    protected function handleUnregisteredUserMessage(Stringable $text): void
    {
        if ($text->toString() === '/start') {
            $this->showRegistrationPrompt();

            return;
        }

        $this->chat->message('🚫 Botdan foydalanish uchun ro\'yxatdan o\'ting.')
            ->keyboard(Keyboard::make()->buttons([
                Button::make('📝 Ro\'yxatdan o\'tish')->action('register'),
            ]))
            ->send();
    }

    protected function unknownCommand(): void
    {
        $this->chat->message('❓ Noma\'lum buyruq. Iltimos, menyudan foydalaning.')->send();
    }

    protected function showMainMenu(): void
    {
        try {
            info('Attempting to send main menu message');
            info('Chat ID: '.$this->chat->chat_id);
            info('Display name: '.($this->chat->display_name ?? 'null'));

            // Force HTTPS for Telegram Web App URL
            $webAppUrl = route('twa.login', $this->chat->chat_id, true);
            $webAppUrl = str_replace('http://', 'https://', $webAppUrl);
            $webAppUrl .= '?next=/twa/dashboard';

            info('Web App URL: '.$webAppUrl);

            $response = $this->chat->message("Xush kelibsiz, {$this->chat->display_name}!")
                ->keyboard(Keyboard::make()->buttons([
                    Button::make('📱 Ilovani ochish')->webApp($webAppUrl),
                ]))
                ->send();

            $status = $response->status() ?? 'unknown';
            $body = $response->body() ?? 'no body';
            info('Message response status: '.$status);
            info('Message response body: '.$body);

            if ($status !== 200) {
                throw new \Exception("Telegram API returned status {$status}: {$body}");
            }
        } catch (\Throwable $e) {
            info('showMainMenu() ERROR: '.$e->getMessage());
            info('Stack trace: '.$e->getTraceAsString());
            \Log::error('showMainMenu() exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'chat_id' => $this->chat->chat_id ?? null,
            ]);

            // Try sending a simple message as fallback
            try {
                $this->chat->message('Xush kelibsiz! Botdan foydalanish uchun ilovani oching.')->send();
            } catch (\Throwable $fallbackError) {
                info('Fallback message also failed: '.$fallbackError->getMessage());
            }
        }
    }

    protected function showRegistrationPrompt(): void
    {
        try {
            info('Attempting to send registration prompt');
            $response = $this->chat->message('Assalomu alaykum! Botdan foydalanish uchun ro\'yxatdan o\'ting.')
                ->keyboard(Keyboard::make()->buttons([
                    Button::make('📝 Ro\'yxatdan o\'tish')->action('register'),
                ]))
                ->send();
            info('Registration prompt sent successfully');
        } catch (\Throwable $e) {
            info('showRegistrationPrompt() ERROR: '.$e->getMessage());
            \Log::error('showRegistrationPrompt() exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
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

    protected function processPhoneNumber(string $phone): void
    {
        info('Processing phone number: '.$phone);

        // Clean the phone number by removing the country code (998)
        $cleanPhone = preg_replace('/^\+?998/', '', $phone);
        info('Cleaned phone number: '.$cleanPhone);

        // Find student by phone number
        $student = Student::where('phone', $cleanPhone)
            ->orWhere('parent_phone', $cleanPhone)
            ->first();

        if (! $student) {
            $this->chat->message("❌ Kechirasiz, bu raqam tizimda topilmadi. Iltimos, to'g'ri raqam kiriting yoki administrator bilan bog'laning.")->send();
            $this->requestPhoneNumber();

            return;
        }

        // Update student's telegram chat id
        $student->telegram_chat_id = $this->chat->chat_id;
        $student->save();

        // Update chat state and display name
        $this->chat->state = 'registered';
        $this->chat->display_name = $student->getFullName();
        $this->chat->save();

        $this->chat->message("✅ Muvaffaqiyatli ro'yxatdan o'tdingiz!")->send();
        $this->showMainMenu();
    }

    protected function isRegistered(): bool
    {
        if (! $this->chat->chat_id) {
            return false;
        }

        return Student::where('telegram_chat_id', $this->chat->chat_id)->exists();
    }

    public function handle(Request $request, TelegraphBot $bot): void
    {
        try {
            info('StudentTelegramWebAppHandler: Starting handle method');
            info('Message type: '.($this->message ? 'exists' : 'null'));
            info('Callback query: '.($this->callbackQuery ? 'exists' : 'null'));

            parent::handle($request, $bot);

            info('StudentTelegramWebAppHandler: Finished handle method');
        } catch (\Throwable $e) {
            info('StudentTelegramWebAppHandler ERROR: '.$e->getMessage());
            info('Stack trace: '.$e->getTraceAsString());
            \Log::error('StudentTelegramWebAppHandler exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    protected function deleteCallbackMessage(): void
    {
        if ($this->callbackQuery?->message()?->id()) {
            $this->chat->deleteMessage($this->callbackQuery->message()->id())->send();
        }
    }
}
