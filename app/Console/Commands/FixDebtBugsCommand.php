<?php

namespace App\Console\Commands;

use App\Models\Group;
use App\Models\StudentGroupPayment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class FixDebtBugsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fix-debt-bugs-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $groups = Group::all();

        $fixed_groups = [];
        $fixed_debts = [];
        foreach ($groups as $group) {
            $student_group_payments = StudentGroupPayment::where('group_id', $group->id)->get();
            foreach ($student_group_payments as $student_group_payment) {
                if (strlen($student_group_payment->range) < 10 && $group->payment_type == 'date_to_date') {
                    if ($student_group_payment->amount === $student_group_payment->left_amount) {
                        //add to fixed groups if not exists
                        if (!in_array($group->id, $fixed_groups)) {
                            $fixed_groups[] = $group->id;
                        }
                        //add to fixed debts if not exists
                        if (!in_array($student_group_payment->id, $fixed_debts)) {
                            $fixed_debts[] = $student_group_payment->id;
                        }
                        $student_group_payment->delete();
                    }
                }
                if (strlen($student_group_payment->range) > 8 && $group->payment_type == 'by_count_lessons') {
                    if ($student_group_payment->amount === $student_group_payment->left_amount) {
                        //add to fixed groups if not exists
                        if (!in_array($group->id, $fixed_groups)) {
                            $fixed_groups[] = $group->id;
                        }
                        //add to fixed debts if not exists
                        if (!in_array($student_group_payment->id, $fixed_debts)) {
                            $fixed_debts[] = $student_group_payment->id;
                        }
                        $student_group_payment->delete();
                    }
                }
            }
        }

        // Sites without LOG_SLACK_WEBHOOK_URL skip the Slack summary.
        if ((count($fixed_groups) > 0 || count($fixed_debts) > 0) && config('logging.channels.slack.url')) {
            Log::channel('slack')->info(config('app.name') . ' fixes: ', [
                'fixed_groups' => json_encode($fixed_groups),
                'fixed_debts' => json_encode($fixed_debts),
            ]);
        }

        $this->info('Done');
    }
}
