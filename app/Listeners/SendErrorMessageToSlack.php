<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Log;

class SendErrorMessageToSlack
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        if(!config('services.slack.url')) {
            return;
        }
        Log::channel('slack')->error($event->error->getMessage(), [
            'app' => config('app.name'),
            'url' => url()->current(),
            'user' => auth()->user() ?? 'Guest',
            'file' => $event->error->getFile(),
            'line' => $event->error->getLine(),
            'trace' => $event->error->getTraceAsString(),
        ]);
    }
}
