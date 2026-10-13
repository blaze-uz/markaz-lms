<?php

namespace App\Livewire\Telegram;

use App\Jobs\SendTelegramMessage;
use DefStudio\Telegraph\Models\TelegraphBot;
use Livewire\Component;

class SendTelegram extends Component
{
    public $receivers = [];

    public $message = '';

    public $isBotConfigured = false;

    public function mount($receivers)
    {
        $this->receivers = $receivers;
        $this->checkBotConfiguration();
    }

    public function checkBotConfiguration(): void
    {
        $studentBotToken = settings('student_telegram_bot_token');

        if (empty($studentBotToken)) {
            $this->isBotConfigured = false;

            return;
        }

        $bot = TelegraphBot::where('token', $studentBotToken)->first();
        $this->isBotConfigured = (bool) $bot;
    }

    public function sendMessage()
    {
        abort_unless(auth()->user()?->can('sms-store'), 403);

        $this->validate([
            'message' => 'required|string|min:1',
        ], [
            'message.required' => __('translation.telegram.message_required'),
        ]);

        $studentBotToken = settings('student_telegram_bot_token');

        if (empty($studentBotToken)) {
            session()->flash('error_alert', __('translation.telegram.student_bot_not_configured'));

            return;
        }

        $bot = TelegraphBot::where('token', $studentBotToken)->first();

        if (! $bot) {
            session()->flash('error_alert', __('translation.telegram.student_bot_not_found'));

            return;
        }

        $formattedMessage = $this->formatMessage($this->message);
        $branchId = auth()->user()->current_branch_id;
        $createdById = auth()->id();

        $connectedReceivers = collect($this->receivers)->filter(fn ($student) => filled($student->telegram_chat_id));
        $skippedCount = count($this->receivers) - $connectedReceivers->count();

        if ($connectedReceivers->isEmpty()) {
            session()->flash('error_alert', __('translation.telegram.no_connected_receivers'));

            return;
        }

        $dispatchedCount = 0;
        $delay = 0; // Delay in seconds for staggering job dispatch

        foreach ($connectedReceivers as $student) {
            // Get student's branch or use current user's branch
            $studentBranchId = $student->branches()->first()?->id ?? $branchId;

            // Dispatch job with delay to prevent overwhelming the queue
            // Telegram allows 30 messages/second, so we stagger by 0.05 seconds (50ms) = 20 messages/second
            SendTelegramMessage::dispatch(
                $student->id,
                $formattedMessage,
                $bot->id,
                $studentBranchId,
                $createdById
            )->delay(now()->addSeconds($delay))->onQueue('sms');

            $dispatchedCount++;
            $delay += 0.05; // 50ms delay between dispatches = 20 messages/second max
        }

        $this->message = '';

        $successMessage = __('translation.telegram.messages_queued', ['count' => $dispatchedCount]);

        if ($skippedCount > 0) {
            $successMessage .= ' '.__('translation.telegram.skipped_not_connected', ['count' => $skippedCount]);
        }

        session()->flash('success_alert', $successMessage);

        // Redirect to telegram messages index
        return redirect()->route('admin.telegram.messages.index');
    }

    private function formatMessage(string $message): string
    {
        return $message;
    }

    public function render()
    {
        return view('livewire.telegram.send-telegram');
    }
}
