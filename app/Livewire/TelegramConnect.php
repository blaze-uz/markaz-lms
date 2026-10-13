<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class TelegramConnect extends Component
{
    public bool $is_connected = false;
    public bool $is_connected_status = false;
    public $user;
    public $telegram_connection_token;
    public $qr;
    public $redirect_url;
    public $deleteId;

    protected const TOKEN_EXPIRY = 3; // minutes
    protected const QR_SIZE = 100; // pixels

    public function mount()
    {
        $this->user = Auth::user();
        $this->refreshConnectionStatus();
    }

    protected function refreshConnectionStatus()
    {
        $this->is_connected = $this->user->user_telegrams()
            ->where('status', 'active')
            ->exists();
        $this->is_connected_status = $this->is_connected;
    }

    public function toggleConnection()
    {
        if ($this->is_connected_status) {
            $this->disconnectTelegram();
        } else {
            $this->connectTelegram();
        }
    }

    protected function disconnectTelegram()
    {
        $this->user->user_telegrams()->update(['status' => 'inactive']);
        $this->is_connected = false;
        $this->is_connected_status = false;
    }

    protected function connectTelegram()
    {
        $telegramConnection = $this->user->user_telegrams()->first();

        if ($telegramConnection) {
            $this->activateExistingConnection($telegramConnection);
        } else {
            $this->initiateNewConnection();
        }
    }

    protected function activateExistingConnection($connection)
    {
        $connection->update(['status' => 'active']);
        $this->is_connected = true;
        $this->is_connected_status = true;
    }

    protected function initiateNewConnection()
    {
        $this->generateConnectionToken();
        $this->cacheUserConnection();

        if ($this->generateQrCode()) {
            $this->dispatch('openTelegramConnect');
        }

        $this->is_connected = false;
        $this->is_connected_status = false;
    }

    protected function generateConnectionToken()
    {
        $this->telegram_connection_token = rand(1000, 9999) . time();
    }

    protected function cacheUserConnection()
    {
        Cache::put(
            $this->telegram_connection_token,
            $this->user->id,
            now()->addMinutes(self::TOKEN_EXPIRY)
        );
    }

    protected function generateQrCode()
    {
        $bot_username = $this->getBotUserName();
        if (!$bot_username) {
            return false;
        }

        $this->generateRedirectUrl($bot_username);
        $this->generateQrImage();
        return true;
    }

    protected function generateRedirectUrl($bot_username)
    {
        $this->redirect_url = sprintf(
            'https://t.me/%s?start=%s',
            $bot_username,
            $this->telegram_connection_token
        );
    }

    protected function generateQrImage()
    {
        $svg = QrCode::size(self::QR_SIZE)
            ->format('svg')
            ->generate($this->redirect_url);

        $this->qr = 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    public function getBotUserName()
    {
        try {
            $bot_token = settings('telegram_bot_token');
            if (!$bot_token) {
                $this->showError('Telegram bot token not configured');
                return null;
            }

            $response = $this->makeApiRequest($bot_token);
            if (!$this->isValidResponse($response)) {
                return null;
            }

            return $response['result']['username'];
        } catch (\Exception $e) {
            $this->showError('Failed to connect to Telegram API');
            return null;
        }
    }

    protected function makeApiRequest($bot_token)
    {
        return Http::get(sprintf(
            'https://api.telegram.org/bot%s/getMe',
            $bot_token
        ));
    }

    protected function isValidResponse($response)
    {
        if (!$response['ok']) {
            $this->showError($response['description']);
            return false;
        }
        return true;
    }

    protected function showError($message)
    {
        $this->dispatch('showError', ['message' => $message]);
    }

    public function deleteTelegram($id)
    {
        $this->user->user_telegrams()->where('id', $id)->delete();
        if ($this->user->user_telegrams()->count() == 0) {
            $this->disconnectTelegram();
        }
        $this->dispatch('showSuccess', ['message' => 'Telegram deleted successfully']);
    }

    public function render()
    {
        $telegrams = $this->user->user_telegrams()->get();
        return view('livewire.telegram-connect', [
            'telegrams' => $telegrams
        ]);
    }
}
