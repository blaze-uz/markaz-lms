<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use App\Models\Expense;
use App\Models\Group;
use App\Models\Lead;
use App\Models\Payment;
use App\Models\Student;
use App\Models\TelegramTemplate;
use App\Traits\Telegram;
use Illuminate\Console\Command;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Carbon;

class DailyMonitorCommand extends Command implements ShouldQueue
{
    use Telegram;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:daily-monitor-command {date?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        // Get date if it is not exist equal to today
        $date = Carbon::parse($this->argument('date')) ?? today();
        $telegram_template = TelegramTemplate::with('telegrams')->where('type', 'daily_report')->first();

        if (! $telegram_template) {
            return;
        }

        // Students statistics
        $new_students = Student::whereDate('created_at', $date)->count();
        $total_students = Student::count();
        $no_group_students = Student::doesntHave('groups')->count();
        $active_students = $total_students - $no_group_students;

        // Leads statistics
        $new_leads = Lead::whereDate('created_at', $date)->count();
        $total_leads = Lead::count();

        $new_groups = Group::whereDate('created_at', $date)->count();
        $payments_count = Payment::whereDate('date', $date)->doesntHave('refunds')->count();
        $refunds_count = Payment::whereDate('created_at', $date)->whereHas('refunds')->count();
        $expenses_amount = Expense::whereDate('date', $date)->sum('price');
        $payments_sum = Payment::whereDate('created_at', $date)->doesntHave('refunds')->sum('amount');
        $date_payments = Payment::whereDate('date', $date)->doesntHave('refunds')->get();
        $refunds_sum = Payment::whereDate('created_at', $date)->whereHas('refunds')->sum('amount');
        $additional_payments = Payment::whereDate('created_at', $date)
            ->whereDate('date', '!=', $date)
            ->doesntHave('refunds')
            ->selectRaw('date, SUM(amount) as total_amount')
            ->groupBy('date')
            ->get();
        $expenses_amount = money($expenses_amount);
        $payments_sum = money($payments_sum);
        $refunds_sum = money($refunds_sum);
        $date = $date->format('d.m.Y');
        $attendancePercentage = $this->getAttendancePercentage($date);

        if ($additional_payments->count() > 0) {
            $additional_payments_text = '      '.Carbon::parse($date)->format('d.m.Y').': '.money($date_payments->sum('amount')).",\n";
            $additional_payments_text .= $additional_payments->map(function ($payment) {
                return '      '.Carbon::parse($payment->date)->format('d.m.Y').': '.money($payment->total_amount);
            })->implode(",\n");
        } else {
            $additional_payments_text = '';
        }

        $data = [
            '📊 Statistika:',
            "➖ Jami lidlar: $total_leads",
            "➖ Bugungi yangi lidlar: $new_leads",
            "➖ Jami talabalar: $total_students",
            "➖ Faol talabalar: $active_students",
            "➖ Yangi talabalar: $new_students",
            '',
            "📝 Kunlik ma'lumotlar:",
            "👨‍🎓 Yangi guruhlar: $new_groups",
            "7️⃣ To'lovlar soni: $payments_count",
            "💰 To'lovlar summasi: ".(empty($additional_payments_text) ? $payments_sum : "$payments_sum,\n$additional_payments_text"),
            "🔄 Qaytarilgan to'lovlar soni: $refunds_count",
            "🔄 Qaytarilgan to'lovlar summasi: $refunds_sum",
            "💸 Xarajatlar summasi: $expenses_amount",
            "📊 Davomat statistikasi: $attendancePercentage%",
        ];

        $text = "$date - kun  bo'yicha tizim ma'lumotlari\n\n<b>".implode("\n", $data).'</b>';

        foreach ($telegram_template->telegrams as $telegram) {
            $this->send('sendMessage', [
                'chat_id' => $telegram->telegram_id,
                'text' => $text,
                'parse_mode' => 'HTML',
                'disable_web_page_preview' => 1,
            ]);
        }

        $this->info('Daily monitor command run successfully 🚀');
    }

    private function getAttendancePercentage($date): float|int
    {
        $date = Carbon::parse($date);
        $query = Attendance::whereDate('date', $date)->select('date', 'status');
        $present = $query->clone()->where('status', 'present')->count();
        $absent = $query->clone()->where('status', 'absent')->count();
        $total = $present + $absent;
        if ($total == 0) {
            return 0;
        }

        return round(($present / $total) * 100, 0);
    }
}
