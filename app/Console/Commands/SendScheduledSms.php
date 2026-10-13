<?php

namespace App\Console\Commands;

use App\Models\Sms;
use App\Services\SmsBulkDispatchService;
use Illuminate\Console\Command;

class SendScheduledSms extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-scheduled-sms';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send scheduled SMS';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //get all scheduled sms where scheduled_at is less than or equal to now
        $scheduledSms = Sms::where('status', 'scheduled')
            ->where('scheduled_at', '<=', now())
            ->get();

        $smsIds = [];
        foreach ($scheduledSms as $sms) {
            if ($sms->phone == null) {
                continue;
            }
            $smsIds[] = $sms->id;
        }

        if ($smsIds !== []) {
            SmsBulkDispatchService::dispatchFromSmsIds($smsIds);
        }
    }
}
