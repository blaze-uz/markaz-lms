<?php

namespace App\Console;

use App\Models\Settings;
use App\Models\TelegramTemplate;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Carbon;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $this->scheduleDailyReviewCommand($schedule);
        $this->scheduleAttendanceNoticeCommand($schedule);
        $this->scheduleAutoStaffPayments($schedule);
        $this->scheduleTelegramMonitorCommands($schedule);
        $this->scheduleScheduledSmsCommand($schedule);
        $this->scheduleDebtSmsCommand($schedule);
    }

    /**
     * Schedule the daily review command based on settings
     */
    private function scheduleDailyReviewCommand(Schedule $schedule): void
    {
        $dailyReviewCommandRunTime = cache()->rememberForever('daily_review_command_run_time', function () {
            $time = Settings::where('key', 'daily_review_command_run_time')->first();
            if ($time) {
                return $time->value;
            }

            return '01:00';
        });

        $schedule->command('app:daily-review-command')->dailyAt($dailyReviewCommandRunTime);
    }

    /**
     * Schedule the attendance notice command
     */
    private function scheduleAttendanceNoticeCommand(Schedule $schedule): void
    {
        $schedule->command('app:attendance-notice-command')
            ->everyFiveMinutes()
            ->between('07:00', '23:00');
    }

    /**
     * Schedule auto group payments for staff if enabled
     */
    private function scheduleAutoStaffPayments(Schedule $schedule): void
    {
        $autoPaymentForStaff = cache()->rememberForever('auto_payment_for_staff', function () {
            $setting = Settings::where('key', 'auto_payment_for_staff')->first();

            // Check explicitly for value string 'enable' (case-insensitive, trims space), allow only "enable" to return true.
            if ($setting && is_string($setting->value) && strtolower(trim($setting->value)) === 'enable') {
                return true;
            }

            return false;
        });

        if ($autoPaymentForStaff) {
            $schedule->command('payments:process-group-payments')->dailyAt('05:00');
        }
    }

    /**
     * Schedule telegram monitor commands based on active templates
     */
    private function scheduleTelegramMonitorCommands(Schedule $schedule): void
    {
        $telegramTemplates = cache()->rememberForever('telegram_templates', function () {
            return TelegramTemplate::where('status', true)->get();
        });

        if ($telegramTemplates->isEmpty()) {
            $schedule->command('app:daily-monitor-command')->dailyAt('20:00');

            return;
        }

        foreach ($telegramTemplates as $telegramTemplate) {
            $time = Carbon::parse($telegramTemplate->time)->format('H:i');
            $schedule->command('app:daily-monitor-command')->dailyAt($time);
        }
    }

    /**
     * Schedule the SMS sending command
     */
    private function scheduleScheduledSmsCommand(Schedule $schedule): void
    {
        $schedule->command('app:send-scheduled-sms')->everyMinute();
    }

    /**
     * Schedule the debt SMS command based on auto send settings
     */
    private function scheduleDebtSmsCommand(Schedule $schedule): void
    {
        // Check if auto send is enabled
        $autoSendEnabled = cache()->remember('debt_auto_send_enabled', 300, function () {
            $setting = Settings::where('key', 'debt_auto_send_enabled')->first();

            return $setting && $setting->value === '1';
        });

        if ($autoSendEnabled) {
            // Run every hour to check if it's time to send based on configured day and time
            $schedule->command('sms:send-debt')
                ->hourly()
                ->between('06:00', '23:00') // Only run during business hours
                ->withoutOverlapping() // Prevent multiple instances running simultaneously
                ->runInBackground(); // Run in background to avoid blocking other commands
        }
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
