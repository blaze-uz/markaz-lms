<?php

namespace App\Jobs;

use App\Models\Student;
use App\Models\TelegramMessage;
use DefStudio\Telegraph\Models\TelegraphBot;
use DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Support\Facades\Log;

class SendTelegramMessage extends BaseJob
{
    public $queue = 'sms';

    public $tries = 3;

    public $backoff = [5, 15, 30]; // Retry delays in seconds

    protected int $studentId;

    protected string $message;

    protected int $telegramBotId;

    protected ?int $branchId;

    protected int $createdById;

    public function __construct(
        int $studentId,
        string $message,
        int $telegramBotId,
        ?int $branchId,
        int $createdById
    ) {
        $this->studentId = $studentId;
        $this->message = $message;
        $this->telegramBotId = $telegramBotId;
        $this->branchId = $branchId;
        $this->createdById = $createdById;
    }

    public function handle(): void
    {
        // Check Telegram API rate limit (30 messages per second per bot)
        // We'll use a conservative 20 messages per second to avoid hitting limits
        if ($secondsRemaining = isRateLimited('telegram-api-limit')) {
            Log::info('SendTelegramMessage: Rate limit active, releasing job', [
                'student_id' => $this->studentId,
                'seconds_remaining' => $secondsRemaining,
            ]);
            $this->release($secondsRemaining);

            return;
        }

        // Add a small delay to prevent hitting rate limits even if jobs are processed quickly
        // Telegram allows 30 messages/second, so we add 35ms delay = ~28 messages/second max
        usleep(35000); // 35 milliseconds

        $student = Student::find($this->studentId);

        if (! $student) {
            Log::error('SendTelegramMessage: Student not found', [
                'student_id' => $this->studentId,
            ]);

            return;
        }

        // Check if student has Telegram chat ID
        if (! $student->telegram_chat_id) {
            $this->createFailedMessage($student, 'Student does not have Telegram chat ID');

            return;
        }

        $bot = TelegraphBot::find($this->telegramBotId);

        if (! $bot) {
            Log::error('SendTelegramMessage: Bot not found', [
                'bot_id' => $this->telegramBotId,
            ]);
            $this->createFailedMessage($student, 'Telegram bot not found');

            return;
        }

        try {
            $chat = TelegraphChat::where('chat_id', $student->telegram_chat_id)
                ->where('telegraph_bot_id', $bot->id)
                ->first()
                ?? TelegraphChat::forceCreate([
                    'chat_id' => $student->telegram_chat_id,
                    'telegraph_bot_id' => $bot->id,
                    'name' => $student->getFullName(),
                ]);

            // Send message
            $chat->message($this->message)->send();

            // Create success record
            $telegramMessage = TelegramMessage::create([
                'student_id' => $student->id,
                'message' => $this->message,
                'status' => 'sent',
                'chat_id' => $student->telegram_chat_id,
                'telegram_bot_id' => $bot->id,
                'sent_at' => now(),
                'created_by' => $this->createdById,
            ]);

            // Attach to branch
            if ($this->branchId) {
                $telegramMessage->branches()->attach($this->branchId);
            }

            Log::info('SendTelegramMessage: Message sent successfully', [
                'student_id' => $student->id,
                'chat_id' => $student->telegram_chat_id,
            ]);
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();

            // Handle "user never started the bot" — no point retrying
            if (str_contains($errorMessage, 'Forbidden') || str_contains($errorMessage, 'bot was blocked') || str_contains($errorMessage, 'chat not found')) {
                Log::warning('SendTelegramMessage: User has not started the bot', [
                    'student_id' => $student->id,
                    'error' => $errorMessage,
                ]);
                $this->createFailedMessage($student, $errorMessage);

                return;
            }

            // Handle Telegram rate limit (429 or similar)
            if (str_contains($errorMessage, '429') || str_contains($errorMessage, 'Too Many Requests') || str_contains($errorMessage, 'rate limit')) {
                Log::warning('SendTelegramMessage: Telegram rate limit hit', [
                    'student_id' => $student->id,
                    'error' => $errorMessage,
                ]);

                // Set rate limit for 60 seconds
                setRateLimit('telegram-api-limit', 60);
                $this->release(60);

                return;
            }

            // Handle other errors
            Log::error('SendTelegramMessage: Failed to send message', [
                'student_id' => $student->id,
                'chat_id' => $student->telegram_chat_id,
                'error' => $errorMessage,
                'trace' => $e->getTraceAsString(),
            ]);

            $this->createFailedMessage($student, $errorMessage);

            // Re-throw to trigger retry mechanism
            throw $e;
        }
    }

    protected function createFailedMessage(Student $student, string $errorMessage): void
    {
        $bot = TelegraphBot::find($this->telegramBotId);

        $telegramMessage = TelegramMessage::create([
            'student_id' => $student->id,
            'message' => $this->message,
            'status' => 'failed',
            'chat_id' => $student->telegram_chat_id,
            'telegram_bot_id' => $bot?->id,
            'error_log' => $errorMessage,
            'created_by' => $this->createdById,
        ]);

        // Attach to branch
        if ($this->branchId) {
            $telegramMessage->branches()->attach($this->branchId);
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('SendTelegramMessage: Job failed after all retries', [
            'student_id' => $this->studentId,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);

        $student = Student::find($this->studentId);
        if ($student) {
            $this->createFailedMessage($student, "Job failed after {$this->tries} attempts: {$exception->getMessage()}");
        }
    }
}
