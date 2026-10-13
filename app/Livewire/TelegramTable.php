<?php

namespace App\Livewire;

use App\Models\Telegram;
use Livewire\Component;
use Livewire\WithPagination;

class TelegramTable extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $search = '';

    public function placeholder()
    {
        return view('livewire.placeholders.skeleton');
    }
    public function render()
    {
        $telegrams = Telegram::search($this->search)->orderByDesc('id')->paginate(10);
        return view('livewire.telegram-table', [
            'telegrams' => $telegrams
        ]);
    }
}
