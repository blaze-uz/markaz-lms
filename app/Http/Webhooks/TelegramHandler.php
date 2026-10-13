<?php

namespace App\Http\Webhooks;

use DefStudio\Telegraph\Handlers\WebhookHandler;
use DefStudio\Telegraph\Models\TelegraphBot;
use Illuminate\Http\Request;

class TelegramHandler extends WebhookHandler
{
    public function handle(Request $request, TelegraphBot $bot): void
    {
        $handlerClass = match ($bot->name) {
            'Students app' => StudentTelegramWebAppHandler::class,
            'Staff app' => StaffTelegramWebAppHandler::class,
            default => null,
        };

        if ($handlerClass) {
            $handlerInstance = new $handlerClass();
            $handlerInstance->handle($request, $bot);

            return;
        }

        parent::handle($request, $bot);
    }
}
